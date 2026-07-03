{{-- resources/views/purchases/orders/index.blade.php --}}
@extends('layouts.app')
@section('title','Purchase Order')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a><span class="separator">/</span>
    <span class="current">Purchase Order</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Purchase Order</h1><p class="page-subtitle">Kelola pesanan pembelian ke pemasok</p></div>
    <a href="{{ route('purchases.orders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat PO</a>
</div>
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3" style="flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nomor PO atau vendor..." style="flex:1;min-width:200px;">
            <select name="status" class="form-control" style="width:160px;">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','confirmed'=>'Dikonfirmasi','partial'=>'Sebagian','received'=>'Diterima','billed'=>'Ditagih','cancelled'=>'Dibatalkan'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:148px;">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="form-control" style="width:148px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('purchases.orders.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr><th>No. PO</th><th>Tanggal</th><th>Vendor</th><th>Exp. Tiba</th><th style="text-align:right;">Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><a href="{{ route('purchases.orders.show',$order) }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">{{ $order->number }}</a></td>
                    <td>{{ $order->date->format('d/m/Y') }}</td>
                    <td style="font-weight:500;">{{ $order->contact?->name }}</td>
                    <td>{{ $order->expected_date?->format('d/m/Y') ?? '-' }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:600;">Rp {{ number_format($order->total,0,',','.') }}</td>
                    <td><span class="badge badge-{{ $order->status_color }}">{{ ucfirst($order->status) }}</span></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('purchases.orders.show',$order) }}" class="btn btn-icon btn-secondary"><i class="fas fa-eye"></i></a>
                            @if($order->status==='draft')
                            <a href="{{ route('purchases.orders.edit',$order) }}" class="btn btn-icon btn-secondary"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('purchases.orders.confirm',$order) }}">@csrf<button class="btn btn-icon btn-success"><i class="fas fa-check"></i></button></form>
                            @endif
                            <a href="{{ route('purchases.orders.print',$order) }}" class="btn btn-icon btn-secondary" target="_blank"><i class="fas fa-print"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;"><i class="fas fa-shopping-bag" style="font-size:40px;display:block;margin-bottom:12px;"></i>Belum ada Purchase Order</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:13px;color:#64748b;">Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }}</div>
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection