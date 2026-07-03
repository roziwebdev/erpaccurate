{{-- resources/views/master/contacts/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Pelanggan & Vendor')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Pelanggan & Vendor</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Pelanggan & Vendor</h1>
        <p class="page-subtitle">Kelola data kontak pelanggan dan pemasok</p>
    </div>
    <a href="{{ route('contacts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Kontak
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-4 gap-4 mb-6" style="grid-template-columns:repeat(4,1fr);">
    <div class="stat-card primary">
        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ $totalCust }}</div>
        <div class="stat-label">Total Pelanggan</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon warning"><i class="fas fa-store"></i></div>
        <div class="stat-value">{{ $totalVend }}</div>
        <div class="stat-label">Total Pemasok</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
        <div class="stat-value">{{ $contacts->total() }}</div>
        <div class="stat-label">Total Kontak</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon info"><i class="fas fa-layer-group"></i></div>
        <div class="stat-value">{{ $groups->count() }}</div>
        <div class="stat-label">Total Grup</div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3" style="flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama, kode, email, telepon..." style="flex:1;min-width:220px;">
            <select name="type" class="form-control" style="width:160px;">
                <option value="">Semua Tipe</option>
                <option value="customer" {{ request('type')=='customer'?'selected':'' }}>Pelanggan</option>
                <option value="vendor"   {{ request('type')=='vendor'?'selected':'' }}>Pemasok</option>
                <option value="both"     {{ request('type')=='both'?'selected':'' }}>Keduanya</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Reset</a>
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
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Grup</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Kota</th>
                    <th>Top (Hari)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $c)
                <tr>
                    <td><span style="font-family:monospace;font-weight:600;">{{ $c->code }}</span></td>
                    <td>
                        <a href="{{ route('contacts.show',$c) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">{{ $c->name }}</a>
                        @if($c->alias)<div style="font-size:11px;color:#94a3b8;">{{ $c->alias }}</div>@endif
                    </td>
                    <td><span class="badge badge-{{ $c->type_color }}">{{ $c->type_label }}</span></td>
                    <td style="font-size:13px;">{{ $c->group?->name ?? '-' }}</td>
                    <td>{{ $c->phone ?? '-' }}</td>
                    <td style="font-size:13px;">{{ $c->email ?? '-' }}</td>
                    <td>{{ $c->billing_city ?? '-' }}</td>
                    <td style="text-align:center;">{{ $c->payment_term }} hari</td>
                    <td>
                        @if($c->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Non-Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('contacts.show',$c) }}" class="btn btn-icon btn-secondary" title="Lihat"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('contacts.edit',$c) }}" class="btn btn-icon btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('contacts.destroy',$c) }}" onsubmit="return confirm('Hapus kontak ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:48px;color:#94a3b8;">
                        <i class="fas fa-address-book" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                        Belum ada kontak. <a href="{{ route('contacts.create') }}" style="color:#4f46e5;">Tambah sekarang!</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contacts->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:13px;color:#64748b;">Menampilkan {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} dari {{ $contacts->total() }}</div>
        <div>{{ $contacts->links() }}</div>
    </div>
    @endif
</div>
@endsection