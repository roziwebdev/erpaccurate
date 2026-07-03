<?php
// app/Http/Controllers/Sales/SalesOrderController.php
namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $cid   = auth()->user()->company_id;
        $query = SalesOrder::where('company_id', $cid)->with(['contact','createdBy'])->latest();

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('search'))    $query->where(fn($q)=>$q->where('number','like',"%{$request->search}%")->orWhereHas('contact',fn($c)=>$c->where('name','like',"%{$request->search}%")));
        if ($request->filled('date_from')) $query->whereDate('date','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('date','<=',$request->date_to);

        $orders    = $query->paginate(15)->withQueryString();
        $customers = Contact::where('company_id',$cid)->whereIn('type',['customer','both'])->where('is_active',true)->orderBy('name')->get();

        return view('sales.orders.index', compact('orders','customers'));
    }

    public function create()
    {
        $cid        = auth()->user()->company_id;
        $customers  = Contact::where('company_id',$cid)->whereIn('type',['customer','both'])->where('is_active',true)->orderBy('name')->get();
        $products   = Product::where('company_id',$cid)->where('is_sold',true)->where('is_active',true)->with(['unit','stocks'])->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $taxes      = Tax::where('company_id',$cid)->where('is_active',true)->get();
        $nextNumber = $this->generateNumber($cid);

        return view('sales.orders.create', compact('customers','products','warehouses','taxes','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_id'          => 'required|exists:contacts,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|numeric|min:0.0001',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $cid = auth()->user()->company_id;
            [$subtotal, $discountAmount, $taxAmount] = $this->calcTotals($request->items);
            $shipping    = floatval($request->shipping_cost ?? 0);
            $discPercent = floatval($request->discount_percent ?? 0);
            $addDisc     = $subtotal * ($discPercent / 100);
            $discountAmount += $addDisc;
            $total = $subtotal - $discountAmount + $taxAmount + $shipping;

            $order = SalesOrder::create([
                'company_id'      => $cid,
                'contact_id'      => $request->contact_id,
                'warehouse_id'    => $request->warehouse_id,
                'created_by'      => auth()->id(),
                'number'          => $request->number ?? $this->generateNumber($cid),
                'date'            => $request->date,
                'due_date'        => $request->due_date,
                'delivery_date'   => $request->delivery_date,
                'status'          => 'draft',
                'reference'       => $request->reference,
                'notes'           => $request->notes,
                'shipping_address'=> $request->shipping_address,
                'subtotal'        => $subtotal,
                'discount_percent'=> $discPercent,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'shipping_cost'   => $shipping,
                'total'           => $total,
                'currency_code'   => 'IDR',
                'exchange_rate'   => 1,
            ]);

            $this->saveItems($order->id, $request->items);
        });

        return redirect()->route('sales.orders.index')->with('success','Sales Order berhasil dibuat!');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['contact','items.product.unit','items.unit','createdBy','warehouse']);
        return view('sales.orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status,['draft'])) {
            return back()->with('error','Hanya SO berstatus draft yang bisa diedit!');
        }
        $cid        = auth()->user()->company_id;
        $customers  = Contact::where('company_id',$cid)->whereIn('type',['customer','both'])->where('is_active',true)->orderBy('name')->get();
        $products   = Product::where('company_id',$cid)->where('is_sold',true)->where('is_active',true)->with(['unit','stocks'])->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id',$cid)->where('is_active',true)->get();
        $taxes      = Tax::where('company_id',$cid)->where('is_active',true)->get();
        $salesOrder->load('items');
        return view('sales.orders.edit', compact('salesOrder','customers','products','warehouses','taxes'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $request->validate(['contact_id'=>'required','date'=>'required|date','items'=>'required|array|min:1']);
        DB::transaction(function() use ($request,$salesOrder){
            [$subtotal,$discountAmount,$taxAmount] = $this->calcTotals($request->items);
            $shipping    = floatval($request->shipping_cost??0);
            $discPercent = floatval($request->discount_percent??0);
            $discountAmount += $subtotal*($discPercent/100);
            $total = $subtotal-$discountAmount+$taxAmount+$shipping;

            $salesOrder->update([
                'contact_id'=>$request->contact_id,'warehouse_id'=>$request->warehouse_id,
                'date'=>$request->date,'due_date'=>$request->due_date,'delivery_date'=>$request->delivery_date,
                'reference'=>$request->reference,'notes'=>$request->notes,'shipping_address'=>$request->shipping_address,
                'subtotal'=>$subtotal,'discount_percent'=>$discPercent,'discount_amount'=>$discountAmount,
                'tax_amount'=>$taxAmount,'shipping_cost'=>$shipping,'total'=>$total,
            ]);
            $salesOrder->items()->delete();
            $this->saveItems($salesOrder->id,$request->items);
        });
        return redirect()->route('sales.orders.show',$salesOrder)->with('success','SO berhasil diperbarui!');
    }

    public function confirm(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') return back()->with('error','SO tidak dalam status draft!');
        $salesOrder->update(['status'=>'confirmed']);
        return back()->with('success','SO berhasil dikonfirmasi!');
    }

    public function cancel(SalesOrder $salesOrder)
    {
        if (in_array($salesOrder->status,['invoiced','cancelled'])) return back()->with('error','SO tidak bisa dibatalkan!');
        $salesOrder->update(['status'=>'cancelled']);
        return back()->with('success','SO berhasil dibatalkan!');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') return back()->with('error','Hanya SO draft yang bisa dihapus!');
        $salesOrder->items()->delete();
        $salesOrder->delete();
        return redirect()->route('sales.orders.index')->with('success','SO berhasil dihapus!');
    }

    private function calcTotals(array $items): array
    {
        $subtotal=$disc=$tax=0;
        foreach($items as $i){
            $q=floatval($i['quantity']??0); $p=floatval($i['unit_price']??0);
            $d=floatval($i['discount_percent']??0); $t=floatval($i['tax_percent']??0);
            $gross=$q*$p; $da=$gross*($d/100); $sub=$gross-$da; $ta=$sub*($t/100);
            $subtotal+=$gross; $disc+=$da; $tax+=$ta;
        }
        return [$subtotal,$disc,$tax];
    }

    private function saveItems(int $orderId, array $items): void
    {
        foreach($items as $i=>$d){
            $q=floatval($d['quantity']); $p=floatval($d['unit_price']);
            $disc=floatval($d['discount_percent']??0); $tax=floatval($d['tax_percent']??0);
            $gross=$q*$p; $da=$gross*($disc/100); $sub=$gross-$da; $ta=$sub*($tax/100);
            SalesOrderItem::create([
                'sales_order_id'=>$orderId,'product_id'=>$d['product_id'],
                'unit_id'=>$d['unit_id']??null,'warehouse_id'=>$d['warehouse_id']??null,
                'description'=>$d['description']??null,'quantity'=>$q,'unit_price'=>$p,
                'discount_percent'=>$disc,'discount_amount'=>$da,'tax_percent'=>$tax,
                'tax_amount'=>$ta,'total'=>$sub+$ta,'sort_order'=>$i,
            ]);
        }
    }

    private function generateNumber(int $cid): string
    {
        $last = SalesOrder::where('company_id',$cid)->whereYear('created_at',date('Y'))->whereMonth('created_at',date('m'))->orderBy('id','desc')->first();
        $seq  = $last ? (intval(substr($last->number,-4))+1) : 1;
        return sprintf('SO/%s/%s/%04d',date('Y'),date('m'),$seq);
    }
}