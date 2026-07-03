<?php
// app/Http/Controllers/Purchase/PurchaseInvoiceController.php
namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $cid   = auth()->user()->company_id;
        $query = PurchaseInvoice::where('company_id',$cid)->with(['contact','createdBy'])->latest();
        if ($request->filled('status'))    $query->where('status',$request->status);
        if ($request->filled('search'))    $query->where(fn($q)=>$q->where('number','like',"%{$request->search}%")->orWhereHas('contact',fn($c)=>$c->where('name','like',"%{$request->search}%")));
        if ($request->filled('date_from')) $query->whereDate('date','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('date','<=',$request->date_to);
        $invoices = $query->paginate(15)->withQueryString();
        $totalUnpaid = PurchaseInvoice::where('company_id',$cid)->whereIn('status',['posted','partial','overdue'])->sum('remaining_amount');
        $totalPaid   = PurchaseInvoice::where('company_id',$cid)->where('status','paid')->whereMonth('date',now()->month)->sum('total');
        return view('purchases.invoices.index', compact('invoices','totalUnpaid','totalPaid'));
    }

    public function create()
    {
        $cid        = auth()->user()->company_id;
        $vendors    = Contact::where('company_id',$cid)->whereIn('type',['vendor','both'])->where('is_active',true)->orderBy('name')->get();
        $products   = Product::where('company_id',$cid)->where('is_purchased',true)->where('is_active',true)->with('unit')->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $taxes      = Tax::where('company_id',$cid)->where('is_active',true)->get();
        $apAccounts = Account::where('company_id',$cid)->where('sub_type','payable')->where('is_active',true)->get();
        $nextNumber = $this->generateNumber($cid);
        return view('purchases.invoices.create', compact('vendors','products','warehouses','taxes','apAccounts','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate(['contact_id'=>'required','date'=>'required|date','due_date'=>'required|date','ap_account_id'=>'required','items'=>'required|array|min:1']);
        DB::transaction(function() use ($request){
            $cid = auth()->user()->company_id;
            $sub=$disc=$tax=0;
            foreach($request->items as $i){
                $q=floatval($i['quantity']??0);$p=floatval($i['unit_price']??0);
                $d=floatval($i['discount_percent']??0);$t=floatval($i['tax_percent']??0);
                $g=$q*$p;$da=$g*($d/100);$s=$g-$da;$ta=$s*($t/100);
                $sub+=$g;$disc+=$da;$tax+=$ta;
            }
            $shipping=floatval($request->shipping_cost??0);
            $total=$sub-$disc+$tax+$shipping;

            $inv = PurchaseInvoice::create([
                'company_id'=>$cid,'contact_id'=>$request->contact_id,
                'warehouse_id'=>$request->warehouse_id,'created_by'=>auth()->id(),
                'purchase_order_id'=>$request->purchase_order_id,
                'number'=>$request->number??$this->generateNumber($cid),
                'vendor_invoice_number'=>$request->vendor_invoice_number,
                'date'=>$request->date,'due_date'=>$request->due_date,
                'status'=>'draft','reference'=>$request->reference,'notes'=>$request->notes,
                'subtotal'=>$sub,'discount_amount'=>$disc,'tax_amount'=>$tax,
                'shipping_cost'=>$shipping,'total'=>$total,'paid_amount'=>0,'remaining_amount'=>$total,
                'currency_code'=>'IDR','exchange_rate'=>1,'ap_account_id'=>$request->ap_account_id,
            ]);

            foreach($request->items as $i=>$d){
                $q=floatval($d['quantity']);$p=floatval($d['unit_price']);
                $disc2=floatval($d['discount_percent']??0);$tax2=floatval($d['tax_percent']??0);
                $g=$q*$p;$da=$g*($disc2/100);$s=$g-$da;$ta=$s*($tax2/100);
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id'=>$inv->id,'product_id'=>$d['product_id'],
                    'unit_id'=>$d['unit_id']??null,'warehouse_id'=>$d['warehouse_id']??$request->warehouse_id,
                    'description'=>$d['description']??null,'quantity'=>$q,'unit_price'=>$p,
                    'discount_percent'=>$disc2,'discount_amount'=>$da,'tax_percent'=>$tax2,
                    'tax_amount'=>$ta,'total'=>$s+$ta,'sort_order'=>$i,
                ]);
            }
        });
        return redirect()->route('purchases.invoices.index')->with('success','Faktur pembelian berhasil dibuat!');
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['contact','items.product.unit','items.unit','createdBy','warehouse','apAccount']);
        return view('purchases.invoices.show', compact('purchaseInvoice'));
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        if($purchaseInvoice->status!=='draft') return back()->with('error','Hanya faktur draft yang bisa diedit!');
        $cid        = auth()->user()->company_id;
        $vendors    = Contact::where('company_id',$cid)->whereIn('type',['vendor','both'])->where('is_active',true)->orderBy('name')->get();
        $products   = Product::where('company_id',$cid)->where('is_purchased',true)->where('is_active',true)->with('unit')->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $taxes      = Tax::where('company_id',$cid)->where('is_active',true)->get();
        $apAccounts = Account::where('company_id',$cid)->where('sub_type','payable')->where('is_active',true)->get();
        $purchaseInvoice->load('items');
        return view('purchases.invoices.edit', compact('purchaseInvoice','vendors','products','warehouses','taxes','apAccounts'));
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if($purchaseInvoice->status!=='draft') return back()->with('error','Hanya faktur draft yang bisa diedit!');
        // Similar to store logic - update fields and items
        DB::transaction(function() use ($request,$purchaseInvoice){
            $sub=$disc=$tax=0;
            foreach($request->items as $i){
                $q=floatval($i['quantity']??0);$p=floatval($i['unit_price']??0);
                $d=floatval($i['discount_percent']??0);$t=floatval($i['tax_percent']??0);
                $g=$q*$p;$da=$g*($d/100);$s=$g-$da;$ta=$s*($t/100);
                $sub+=$g;$disc+=$da;$tax+=$ta;
            }
            $shipping=floatval($request->shipping_cost??0);
            $total=$sub-$disc+$tax+$shipping;
            $purchaseInvoice->update([
                'contact_id'=>$request->contact_id,'warehouse_id'=>$request->warehouse_id,
                'vendor_invoice_number'=>$request->vendor_invoice_number,
                'date'=>$request->date,'due_date'=>$request->due_date,
                'reference'=>$request->reference,'notes'=>$request->notes,
                'subtotal'=>$sub,'discount_amount'=>$disc,'tax_amount'=>$tax,
                'shipping_cost'=>$shipping,'total'=>$total,'remaining_amount'=>$total-$purchaseInvoice->paid_amount,
                'ap_account_id'=>$request->ap_account_id,
            ]);
            $purchaseInvoice->items()->delete();
            foreach($request->items as $i=>$d){
                $q=floatval($d['quantity']);$p=floatval($d['unit_price']);
                $disc2=floatval($d['discount_percent']??0);$tax2=floatval($d['tax_percent']??0);
                $g=$q*$p;$da=$g*($disc2/100);$s=$g-$da;$ta=$s*($tax2/100);
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id'=>$purchaseInvoice->id,'product_id'=>$d['product_id'],
                    'unit_id'=>$d['unit_id']??null,'warehouse_id'=>$d['warehouse_id']??$request->warehouse_id,
                    'description'=>$d['description']??null,'quantity'=>$q,'unit_price'=>$p,
                    'discount_percent'=>$disc2,'discount_amount'=>$da,'tax_percent'=>$tax2,'tax_amount'=>$ta,'total'=>$s+$ta,'sort_order'=>$i,
                ]);
            }
        });
        return redirect()->route('purchases.invoices.show',$purchaseInvoice)->with('success','Faktur diperbarui!');
    }

    public function post(PurchaseInvoice $purchaseInvoice)
    {
        if($purchaseInvoice->status!=='draft') return back()->with('error','Sudah diposting!');
        DB::transaction(function() use ($purchaseInvoice){
            $purchaseInvoice->load(['items.product','contact','apAccount']);

            $journal = Journal::create([
                'company_id'=>$purchaseInvoice->company_id,'created_by'=>auth()->id(),
                'number'=>'JRN-PUR-'.date('YmdHis'),'date'=>$purchaseInvoice->date,
                'type'=>'purchase','description'=>"Faktur Pembelian #{$purchaseInvoice->number} - {$purchaseInvoice->contact->name}",
                'reference_type'=>PurchaseInvoice::class,'reference_id'=>$purchaseInvoice->id,
                'status'=>'posted','total_debit'=>$purchaseInvoice->total,'total_credit'=>$purchaseInvoice->total,
            ]);

            // Credit AP
            JournalEntry::create([
                'journal_id'=>$journal->id,'account_id'=>$purchaseInvoice->ap_account_id,
                'contact_id'=>$purchaseInvoice->contact_id,
                'description'=>"Hutang - {$purchaseInvoice->number}",'debit'=>0,'credit'=>$purchaseInvoice->total,
            ]);

            foreach($purchaseInvoice->items as $item){
                // Debit Inventory or Expense
                $accountId = $item->product->inventory_account_id ?? $item->product->purchase_account_id;
                if($accountId){
                    JournalEntry::create([
                        'journal_id'=>$journal->id,'account_id'=>$accountId,
                        'description'=>"Pembelian - {$item->product->name}",
                        'debit'=>$item->total-$item->tax_amount,'credit'=>0,
                    ]);
                }

                // Debit PPN Masukan
                if($item->tax_amount > 0){
                    $ppnIn = Account::where('company_id',$purchaseInvoice->company_id)->where('code','1-1600')->first();
                    if($ppnIn){
                        JournalEntry::create([
                            'journal_id'=>$journal->id,'account_id'=>$ppnIn->id,
                            'description'=>"PPN Masukan - {$purchaseInvoice->number}",'debit'=>$item->tax_amount,'credit'=>0,
                        ]);
                    }
                }

                // Update stock
                if($item->product->type==='inventory' && $item->product->track_inventory){
                    $stock = ProductStock::firstOrCreate(
                        ['product_id'=>$item->product_id,'warehouse_id'=>$item->warehouse_id??$purchaseInvoice->warehouse_id],
                        ['quantity'=>0,'avg_cost'=>0]
                    );
                    $totalQty  = $stock->quantity + $item->quantity;
                    $totalCost = ($stock->quantity*$stock->avg_cost) + ($item->quantity*$item->unit_price);
                    $avgCost   = $totalQty > 0 ? $totalCost/$totalQty : $item->unit_price;
                    $stock->update(['quantity'=>$totalQty,'avg_cost'=>$avgCost]);

                    // Update product HPP
                    $item->product->update(['hpp'=>$avgCost,'purchase_price'=>$item->unit_price]);
                }
            }
            $purchaseInvoice->update(['status'=>'posted']);
        });
        return back()->with('success','Faktur pembelian berhasil diposting!');
    }

    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        if(in_array($purchaseInvoice->status,['paid','cancelled'])) return back()->with('error','Tidak bisa dibatalkan!');
        $purchaseInvoice->update(['status'=>'cancelled']);
        return back()->with('success','Faktur dibatalkan!');
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        if($purchaseInvoice->status!=='draft') return back()->with('error','Hanya draft yang bisa dihapus!');
        $purchaseInvoice->items()->delete();
        $purchaseInvoice->delete();
        return redirect()->route('purchases.invoices.index')->with('success','Faktur dihapus!');
    }

    private function generateNumber(int $cid): string
    {
        $last=PurchaseInvoice::where('company_id',$cid)->whereYear('created_at',date('Y'))->whereMonth('created_at',date('m'))->orderBy('id','desc')->first();
        $seq=$last?(intval(substr($last->number,-4))+1):1;
        return sprintf('PINV/%s/%s/%04d',date('Y'),date('m'),$seq);
    }
}