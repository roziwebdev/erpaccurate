{{-- resources/views/inventory/adjustments/index.blade.php --}}
@extends('layouts.app')
@section('title','Penyesuaian Stok')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a><span class="separator">/</span>
    <span class="current">Penyesuaian Stok</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Penyesuaian Stok</h1><p class="page-subtitle">Opname dan penyesuaian jumlah stok</p></div>
    <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Penyesuaian</a>
</div>
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nomor..." style="flex:1;min-width:200px;">
            <select name="status" class="form-control" style="width:150px;">
                <option value="">Semua Status</option>
                <option value="draft"   {{ request('status')==='draft'?'selected':'' }}>Draft</option>
                <option value="posted"  {{ request('status')==='posted'?'selected':'' }}>Diposting</option>
                <option value="cancelled"{{ request('status')==='cancelled'?'selected':'' }}>Dibatalkan</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr><th>Nomor</th><th>Tanggal</th><th>Gudang</th><th>Tipe</th><th>Dibuat Oleh</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td style="font-weight:600;">{{ $adj->number }}</td>
                    <td>{{ $adj->date->format('d/m/Y') }}</td>
                    <td>{{ $adj->warehouse?->name }}</td>
                    <td><span class="badge badge-info">{{ $adj->type_label }}</span></td>
                    <td style="font-size:12px;">{{ $adj->createdBy?->name }}</td>
                    <td><span class="badge badge-{{ $adj->status_color }}">{{ ucfirst($adj->status) }}</span></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('inventory.adjustments.show',$adj) }}" class="btn btn-icon btn-secondary"><i class="fas fa-eye"></i></a>
                            @if($adj->status==='draft')
                            <form method="POST" action="{{ route('inventory.adjustments.post',$adj) }}">@csrf<button class="btn btn-icon btn-success" title="Posting" onclick="return confirm('Posting penyesuaian ini?')"><i class="fas fa-check"></i></button></form>
                            <form method="POST" action="{{ route('inventory.adjustments.destroy',$adj) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-icon btn-danger"><i class="fas fa-trash"></i></button></form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;"><i class="fas fa-edit" style="font-size:40px;display:block;margin-bottom:12px;"></i>Belum ada penyesuaian stok</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($adjustments->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:13px;color:#64748b;">{{ $adjustments->firstItem() }}–{{ $adjustments->lastItem() }} dari {{ $adjustments->total() }}</div>
        {{ $adjustments->links() }}
    </div>
    @endif
</div>
@endsection