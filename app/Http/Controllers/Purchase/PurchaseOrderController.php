<?php
// app/Http/Controllers/Purchase/PurchaseOrderController.php
namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $cid   = auth()->user()->company_id;
        $query = PurchaseOrder::where('company_id',$cid)->with(['contact','createdBy'])->latest();
        if ($request->filled('status'))    $query->where('status',$request->status);
        if ($request->filled('search'))    $query->where(fn($q)=>$q->where('number','like',"%{$request->search}%")->orWhereHas('contact',fn($c)=>$c->where('name','like',"%{$request->search}%")));
        if ($request->filled('date_from')) $query->whereDate('date','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('date','<=',$request->date_to);
        $orders  = $query->paginate(15)->withQueryString();
        $vendors = Contact::where('company_id',$cid)->whereIn('type',['vendor','both'])->where('is_active',true)->orderBy('name')->get();
        return view('purchases.orders.index', compact('orders','vendors'));
    }

    public function create()
    {
        $cid        = auth()->user()->company_id;
        $vendors    = Contact::where('company_id',$cid)->whereIn('type',['vendor','both'])->where('is_active',true)->orderBy('name')->get();
        $products   = Product::where('company_id',$cid)->where('is_purchased',true)->where('is_active',true)->with(['unit'])->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $taxes      = Tax::where('company_id',$cid)->where('is_active',true)->get();
        $nextNumber = $this->generateNumber($cid);
        return view('purchases.orders.create', compact('vendors','products','warehouses','taxes','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate(['contact_id'=>'required','date'=>'required|date','items'=>'required|array|min:1']);
        DB::transaction(function() use ($request){
            $cid = auth()->user()->company_id;
            [$sub,$disc,$tax] = $this->calcTotals($request->items);
            $shipping=$floatval($request->shipping_cost??0);
            $shipping = floatval($request->shipping_cost??0);
            $discP   = floatval($request->discount_percent??0);
            $disc   += $sub*($discP/100);
            $total   = $sub-$disc+$tax+$shipping;

            $order = PurchaseOrder::create([
                'company_id'=>$cid,'contact_id'=>$request->contact_id,
                'warehouse_id'=>$request->warehouse_id,'created_by'=>auth()->id(),
                'number'=>$request->number??$this->generateNumber($cid),
                'date'=>$request->date,'due_date'=>$request->due_date,'expected_date'=>$request->expected_date,
                'status'=>'draft','reference'=>$request->reference,'notes'=>$request->notes,
                'subtotal'=>$sub,'discount_percent'=>$discP,'discount_amount'=>$disc,
                'tax_amount'=>$tax,'shipping_cost'=>$shipping,'total'=>$total,'currency_code'=>'IDR','exchange_rate'=>1,
            ]);
            $this->saveItems($order->id,$request->items);
        });
        return redirect()->route('purchases.orders.index')->with('success','Purchase Order berhasil dibuat!');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['contact','items.product.unit','items.unit','createdBy','warehouse']);
        return view('purchases.orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') return back()->with('error','Hanya PO draft yang bisa diedit!');
        $cid        = auth()->user()->company_id;
        $vendors    = Contact::where('company_id',$cid)->whereIn('type',['vendor','both'])->where('is_active',true)->orderBy('name')->get();
        $products   = Product::where('company_id',$cid)->where('is_purchased',true)->where('is_active',true)->with('unit')->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $taxes      = Tax::where('company_id',$cid)->where('is_active',true)->get();
        $purchaseOrder->load('items');
        return view('purchases.orders.edit', compact('purchaseOrder','vendors','products','warehouses','taxes'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate(['contact_id'=>'required','date'=>'required|date','items'=>'required|array|min:1']);
        DB::transaction(function() use ($request,$purchaseOrder){
            [$sub,$disc,$tax]=$this->calcTotals($request->items);
            $shipping=floatval($request->shipping_cost??0);
            $discP=floatval($request->discount_percent??0);
            $disc+=$sub*($discP/100);
            $total=$sub-$disc+$tax+$shipping;
            $purchaseOrder->update([
                'contact_id'=>$request->contact_id,'warehouse_id'=>$request->warehouse_id,
                'date'=>$request->date,'due_date'=>$request->due_date,'expected_date'=>$request->expected_date,
                'reference'=>$request->reference,'notes'=>$request->notes,
                'subtotal'=>$sub,'discount_percent'=>$discP,'discount_amount'=>$disc,'tax_amount'=>$tax,'shipping_cost'=>$shipping,'total'=>$total,
            ]);
            $purchaseOrder->items()->delete();
            $this->saveItems($purchaseOrder->id,$request->items);
        });
        return redirect()->route('purchases.orders.show',$purchaseOrder)->with('success','PO berhasil diperbarui!');
    }

    public function confirm(PurchaseOrder $purchaseOrder)
    {
        if($purchaseOrder->status!=='draft') return back()->with('error','PO tidak dalam status draft!');
        $purchaseOrder->update(['status'=>'confirmed']);
        return back()->with('success','PO berhasil dikonfirmasi!');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if(in_array($purchaseOrder->status,['billed','cancelled'])) return back()->with('error','PO tidak bisa dibatalkan!');
        $purchaseOrder->update(['status'=>'cancelled']);
        return back()->with('success','PO berhasil dibatalkan!');
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['contact','items.product.unit','createdBy','company']);
        return view('purchases.orders.print', compact('purchaseOrder'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if($purchaseOrder->status!=='draft') return back()->with('error','Hanya PO draft yang bisa dihapus!');
        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();
        return redirect()->route('purchases.orders.index')->with('success','PO berhasil dihapus!');
    }

    private function calcTotals(array $items): array
    {
        $sub=$disc=$tax=0;
        foreach($items as $i){
            $q=floatval($i['quantity']??0);$p=floatval($i['unit_price']??0);
            $d=floatval($i['discount_percent']??0);$t=floatval($i['tax_percent']??0);
            $g=$q*$p;$da=$g*($d/100);$s=$g-$da;$ta=$s*($t/100);
            $sub+=$g;$disc+=$da;$tax+=$ta;
        }
        return[$sub,$disc,$tax];
    }

    private function saveItems(int $id, array $items): void
    {
        foreach($items as $i=>$d){
            $q=floatval($d['quantity']);$p=floatval($d['unit_price']);
            $disc=floatval($d['discount_percent']??0);$tax=floatval($d['tax_percent']??0);
            $g=$q*$p;$da=$g*($disc/100);$s=$g-$da;$ta=$s*($tax/100);
            PurchaseOrderItem::create([
                'purchase_order_id'=>$id,'product_id'=>$d['product_id'],
                'unit_id'=>$d['unit_id']??null,'description'=>$d['description']??null,
                'quantity'=>$q,'unit_price'=>$p,'discount_percent'=>$disc,'discount_amount'=>$da,
                'tax_percent'=>$tax,'tax_amount'=>$ta,'total'=>$s+$ta,'sort_order'=>$i,
            ]);
        }
    }

    private function generateNumber(int $cid): string
    {
        $last=PurchaseOrder::where('company_id',$cid)->whereYear('created_at',date('Y'))->whereMonth('created_at',date('m'))->orderBy('id','desc')->first();
        $seq=$last?(intval(substr($last->number,-4))+1):1;
        return sprintf('PO/%s/%s/%04d',date('Y'),date('m'),$seq);
    }
}