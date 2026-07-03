{{-- resources/views/sales/orders/index.blade.php --}}
@extends('layouts.app')
@section('title','Sales Order')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a><span class="separator">/</span>
    <span class="current">Sales Order</span>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sales Order</h1>
        <p class="page-subtitle">Kelola pesanan penjualan dari pelanggan</p>
    </div>
    <a href="{{ route('sales.orders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat SO</a>
</div>

<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3" style="flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nomor SO atau pelanggan..." style="flex:1;min-width:200px;">
            <select name="status" class="form-control" style="width:160px;">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','confirmed'=>'Dikonfirmasi','partial'=>'Sebagian','delivered'=>'Terkirim','invoiced'=>'Ditagih','cancelled'=>'Dibatalkan'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:148px;">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="form-control" style="width:148px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('sales.orders.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>No. SO</th><th>Tanggal</th><th>Pelanggan</th>
                    <th style="text-align:right;">Total</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><a href="{{ route('sales.orders.show',$order) }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">{{ $order->number }}</a></td>
                    <td>{{ $order->date->format('d/m/Y') }}</td>
                    <td style="font-weight:500;">{{ $order->contact?->name }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:600;">Rp {{ number_format($order->total,0,',','.') }}</td>
                    <td><span class="badge badge-{{ $order->status_color }}">{{ ucfirst($order->status) }}</span></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('sales.orders.show',$order) }}" class="btn btn-icon btn-secondary"><i class="fas fa-eye"></i></a>
                            @if($order->status==='draft')
                            <a href="{{ route('sales.orders.edit',$order) }}" class="btn btn-icon btn-secondary"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('sales.orders.confirm',$order) }}">@csrf<button type="submit" class="btn btn-icon btn-success" title="Konfirmasi"><i class="fas fa-check"></i></button></form>
                            @endif
                            @if(!in_array($order->status,['invoiced','cancelled']))
                            <form method="POST" action="{{ route('sales.orders.cancel',$order) }}">@csrf<button type="submit" class="btn btn-icon btn-danger" title="Batalkan" onclick="return confirm('Batalkan SO ini?')"><i class="fas fa-ban"></i></button></form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:48px;color:#94a3b8;"><i class="fas fa-file-alt" style="font-size:40px;display:block;margin-bottom:12px;"></i>Belum ada Sales Order</td></tr>
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