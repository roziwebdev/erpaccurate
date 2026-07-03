<?php
// app/Http/Controllers/Inventory/StockAdjustmentController.php
namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $cid  = auth()->user()->company_id;
        $query= StockAdjustment::where('company_id',$cid)->with(['warehouse','createdBy'])->latest();
        if ($request->filled('status'))   $query->where('status',$request->status);
        if ($request->filled('search'))   $query->where('number','like',"%{$request->search}%");
        $adjustments = $query->paginate(15)->withQueryString();
        return view('inventory.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $cid        = auth()->user()->company_id;
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $products   = Product::where('company_id',$cid)->where('type','inventory')->where('is_active',true)->with(['unit','stocks'])->orderBy('name')->get();
        $accounts   = Account::where('company_id',$cid)->where('is_active',true)->where('is_header',false)->orderBy('code')->get();
        $nextNumber = 'ADJ/'.date('Y/m/').str_pad(StockAdjustment::where('company_id',$cid)->whereMonth('created_at',now()->month)->count()+1,4,'0',STR_PAD_LEFT);
        return view('inventory.adjustments.create', compact('warehouses','products','accounts','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate(['warehouse_id'=>'required','date'=>'required|date','type'=>'required','items'=>'required|array|min:1']);
        DB::transaction(function() use ($request){
            $cid = auth()->user()->company_id;
            $adj = StockAdjustment::create([
                'company_id'=>$cid,'warehouse_id'=>$request->warehouse_id,'created_by'=>auth()->id(),
                'number'=>$request->number,'date'=>$request->date,'status'=>'draft',
                'type'=>$request->type,'reference'=>$request->reference,'notes'=>$request->notes,
                'account_id'=>$request->account_id,
            ]);
            foreach($request->items as $i=>$d){
                $stock = ProductStock::where('product_id',$d['product_id'])->where('warehouse_id',$request->warehouse_id)->first();
                $qtySystem = $stock?->quantity ?? 0;
                $qtyActual = floatval($d['qty_actual']);
                $diff      = $qtyActual - $qtySystem;
                $cost      = $stock?->avg_cost ?? 0;
                StockAdjustmentItem::create([
                    'stock_adjustment_id'=>$adj->id,'product_id'=>$d['product_id'],
                    'unit_id'=>$d['unit_id']??null,'description'=>$d['description']??null,
                    'qty_system'=>$qtySystem,'qty_actual'=>$qtyActual,'qty_difference'=>$diff,
                    'unit_cost'=>$cost,'total_cost'=>abs($diff*$cost),'sort_order'=>$i,
                ]);
            }
        });
        return redirect()->route('inventory.adjustments.index')->with('success','Penyesuaian stok berhasil disimpan!');
    }

    public function show(StockAdjustment $adjustment)
    {
        $adjustment->load(['warehouse','items.product.unit','items.unit','createdBy']);
        return view('inventory.adjustments.show', compact('adjustment'));
    }

    public function post(StockAdjustment $adjustment)
    {
        if($adjustment->status!=='draft') return back()->with('error','Sudah diposting!');
        DB::transaction(function() use ($adjustment){
            $adjustment->load(['items.product','warehouse']);

            $totalDebit=$totalCredit=0;
            $journalEntries=[];

            foreach($adjustment->items as $item){
                $stock = ProductStock::firstOrCreate(
                    ['product_id'=>$item->product_id,'warehouse_id'=>$adjustment->warehouse_id],
                    ['quantity'=>0,'avg_cost'=>$item->unit_cost]
                );
                $diff = $item->qty_difference;
                $cost = abs($diff * $item->unit_cost);

                $stock->update(['quantity'=>$item->qty_actual]);

                if($adjustment->account_id && $item->product->inventory_account_id){
                    if($diff > 0){
                        $journalEntries[]=['account_id'=>$item->product->inventory_account_id,'debit'=>$cost,'credit'=>0,'desc'=>"Penambahan stok - {$item->product->name}"];
                        $journalEntries[]=['account_id'=>$adjustment->account_id,'debit'=>0,'credit'=>$cost,'desc'=>"Selisih stok - {$item->product->name}"];
                        $totalDebit+=$cost; $totalCredit+=$cost;
                    } elseif($diff < 0){
                        $journalEntries[]=['account_id'=>$adjustment->account_id,'debit'=>$cost,'credit'=>0,'desc'=>"Selisih stok - {$item->product->name}"];
                        $journalEntries[]=['account_id'=>$item->product->inventory_account_id,'debit'=>0,'credit'=>$cost,'desc'=>"Pengurangan stok - {$item->product->name}"];
                        $totalDebit+=$cost; $totalCredit+=$cost;
                    }
                }
            }

            if(!empty($journalEntries) && $totalDebit>0){
                $journal = Journal::create([
                    'company_id'=>$adjustment->company_id,'created_by'=>auth()->id(),
                    'number'=>'JRN-ADJ-'.date('YmdHis'),'date'=>$adjustment->date,
                    'type'=>'adjustment','description'=>"Penyesuaian Stok #{$adjustment->number}",
                    'reference_type'=>StockAdjustment::class,'reference_id'=>$adjustment->id,
                    'status'=>'posted','total_debit'=>$totalDebit,'total_credit'=>$totalCredit,
                ]);
                foreach($journalEntries as $e){
                    JournalEntry::create(['journal_id'=>$journal->id,'account_id'=>$e['account_id'],'description'=>$e['desc'],'debit'=>$e['debit'],'credit'=>$e['credit']]);
                }
            }

            $adjustment->update(['status'=>'posted']);
        });
        return back()->with('success','Penyesuaian stok berhasil diposting!');
    }

    public function destroy(StockAdjustment $adjustment)
    {
        if($adjustment->status!=='draft') return back()->with('error','Hanya draft yang bisa dihapus!');
        $adjustment->items()->delete();
        $adjustment->delete();
        return redirect()->route('inventory.adjustments.index')->with('success','Penyesuaian dihapus!');
    }
}