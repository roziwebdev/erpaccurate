{{-- resources/views/inventory/products/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Produk & Item')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Produk & Item</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Produk & Item</h1>
        <p class="page-subtitle">Kelola data produk, jasa, dan inventori</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('inventory.products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
        <div class="dropdown">
            <button class="btn btn-secondary" onclick="toggleDropdown('import-menu')">
                <i class="fas fa-upload"></i> Import <i class="fas fa-chevron-down" style="font-size:10px;"></i>
            </button>
            <div class="dropdown-menu" id="import-menu">
                <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Import dari Excel</a>
                <a href="#" class="dropdown-item"><i class="fas fa-download"></i> Download Template</a>
            </div>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="stat-card primary">
        <div class="stat-icon primary"><i class="fas fa-boxes"></i></div>
        <div class="stat-value">{{ $products->total() }}</div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon success"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValue/1000000,1) }}Jt</div>
        <div class="stat-label">Nilai Inventori</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon warning"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value">{{ $products->getCollection()->filter(fn($p)=>$p->total_stock<=$p->min_stock && $p->min_stock>0)->count() }}</div>
        <div class="stat-label">Stok Menipis</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon info"><i class="fas fa-concierge-bell"></i></div>
        <div class="stat-value">{{ $products->getCollection()->where('type','service')->count() }}</div>
        <div class="stat-label">Item Jasa</div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3" style="flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari kode, nama, barcode..." style="flex:1;min-width:200px;">
            <select name="type" class="form-control" style="width:160px;">
                <option value="">Semua Tipe</option>
                <option value="inventory"     {{ request('type')=='inventory'?'selected':'' }}>Persediaan</option>
                <option value="service"       {{ request('type')=='service'?'selected':'' }}>Jasa</option>
                <option value="non_inventory" {{ request('type')=='non_inventory'?'selected':'' }}>Non-Persediaan</option>
            </select>
            <select name="category" class="form-control" style="width:180px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th style="text-align:right;">Harga Jual</th>
                    <th style="text-align:right;">Harga Beli</th>
                    <th style="text-align:right;">Stok</th>
                    <th style="text-align:right;">Min Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><span style="font-family:monospace;font-weight:600;font-size:12px;">{{ $product->code }}</span></td>
                    <td>
                        <div style="font-weight:500;">{{ $product->name }}</div>
                        @if($product->barcode)<div style="font-size:11px;color:#94a3b8;font-family:monospace;">{{ $product->barcode }}</div>@endif
                    </td>
                    <td><span class="badge badge-{{ $product->type_color }}">{{ $product->type_label }}</span></td>
                    <td style="font-size:13px;">{{ $product->category?->name ?? '-' }}</td>
                    <td style="text-align:center;">{{ $product->unit?->symbol ?? '-' }}</td>
                    <td style="text-align:right;font-family:monospace;">Rp {{ number_format($product->selling_price,0,',','.') }}</td>
                    <td style="text-align:right;font-family:monospace;">Rp {{ number_format($product->purchase_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;">
                        @if($product->type === 'inventory')
                            @php $stock = $product->total_stock; $low = $stock <= $product->min_stock && $product->min_stock > 0; @endphp
                            <span style="color:{{ $low ? '#ef4444' : '#10b981' }}">
                                {{ number_format($stock,2) }}
                                @if($low)<i class="fas fa-exclamation-triangle" style="font-size:11px;"></i>@endif
                            </span>
                        @else
                            <span style="color:#94a3b8;">-</span>
                        @endif
                    </td>
                    <td style="text-align:right;color:#94a3b8;">
                        {{ $product->min_stock > 0 ? number_format($product->min_stock,2) : '-' }}
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Non-Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('inventory.products.show',$product) }}" class="btn btn-icon btn-secondary" title="Lihat"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('inventory.products.edit',$product) }}" class="btn btn-icon btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('inventory.products.destroy',$product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:48px;color:#94a3b8;">
                        <i class="fas fa-box-open" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                        Belum ada produk. <a href="{{ route('inventory.products.create') }}" style="color:#4f46e5;">Tambah sekarang!</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:13px;color:#64748b;">Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }}</div>
        <div>{{ $products->links() }}</div>
    </div>
    @endif
</div>
@endsection