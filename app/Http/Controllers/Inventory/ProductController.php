<?php
// app/Http/Controllers/Inventory/ProductController.php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $query = Product::where('company_id',$companyId)->with(['category','unit','stocks'])->latest();

        if ($request->filled('type'))     $query->where('type',$request->type);
        if ($request->filled('category')) $query->where('product_category_id',$request->category);
        if ($request->filled('search'))   $query->where(fn($q)=>$q->where('name','like',"%{$request->search}%")->orWhere('code','like',"%{$request->search}%")->orWhere('barcode','like',"%{$request->search}%"));

        $products   = $query->paginate(15)->withQueryString();
        $categories = ProductCategory::where('company_id',$companyId)->get();
        $totalValue = Product::where('company_id',$companyId)->where('type','inventory')
            ->with('stocks')->get()->sum(fn($p)=>$p->total_stock * $p->hpp);

        return view('inventory.products.index', compact('products','categories','totalValue'));
    }

    public function create()
    {
        $companyId  = auth()->user()->company_id;
        $categories = ProductCategory::where('company_id',$companyId)->get();
        $units      = Unit::where('company_id',$companyId)->get();
        $warehouses = Warehouse::where('company_id',$companyId)->where('is_active',true)->get();
        $salesAccounts    = Account::where('company_id',$companyId)->whereIn('sub_type',['sales','other_revenue'])->where('is_active',true)->get();
        $purchaseAccounts = Account::where('company_id',$companyId)->whereIn('sub_type',['cogs','operating_expense'])->where('is_active',true)->get();
        $inventoryAccounts= Account::where('company_id',$companyId)->where('sub_type','inventory')->where('is_active',true)->get();
        $nextCode = 'PRD-'.str_pad(Product::where('company_id',$companyId)->count()+1,4,'0',STR_PAD_LEFT);

        return view('inventory.products.create', compact('categories','units','warehouses','salesAccounts','purchaseAccounts','inventoryAccounts','nextCode'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $request->validate([
            'code'          => "required|unique:products,code,NULL,id,company_id,{$companyId}",
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:inventory,service,non_inventory',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product = Product::create(array_merge($request->except(['_token','opening_stock_warehouse']),['company_id'=>$companyId]));

        // Init stock in warehouse
        if ($request->type === 'inventory' && floatval($request->opening_stock) > 0 && $request->opening_stock_warehouse) {
            ProductStock::create([
                'product_id'   => $product->id,
                'warehouse_id' => $request->opening_stock_warehouse,
                'quantity'     => $request->opening_stock,
                'avg_cost'     => $request->purchase_price ?? 0,
            ]);
        }

        return redirect()->route('inventory.products.index')->with('success','Produk berhasil ditambahkan!');
    }

    public function show(Product $product)
    {
        $product->load(['category','unit','stocks.warehouse']);
        return view('inventory.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $companyId  = auth()->user()->company_id;
        $categories = ProductCategory::where('company_id',$companyId)->get();
        $units      = Unit::where('company_id',$companyId)->get();
        $salesAccounts    = Account::where('company_id',$companyId)->whereIn('sub_type',['sales','other_revenue'])->where('is_active',true)->get();
        $purchaseAccounts = Account::where('company_id',$companyId)->whereIn('sub_type',['cogs','operating_expense'])->where('is_active',true)->get();
        $inventoryAccounts= Account::where('company_id',$companyId)->where('sub_type','inventory')->where('is_active',true)->get();

        return view('inventory.products.edit', compact('product','categories','units','salesAccounts','purchaseAccounts','inventoryAccounts'));
    }

    public function update(Request $request, Product $product)
    {
        $companyId = auth()->user()->company_id;
        $request->validate([
            'code'          => "required|unique:products,code,{$product->id},id,company_id,{$companyId}",
            'name'          => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product->update($request->except(['_token','_method','opening_stock_warehouse']));

        return redirect()->route('inventory.products.show',$product)->with('success','Produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('inventory.products.index')->with('success','Produk berhasil dihapus!');
    }

    public function stockCard(Product $product)
    {
        $product->load(['unit','stocks.warehouse']);
        // Stock movements from journal entries etc.
        return view('inventory.products.stock-card', compact('product'));
    }
}