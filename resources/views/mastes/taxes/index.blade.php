{{-- resources/views/master/taxes/index.blade.php --}}
@extends('layouts.app')
@section('title','Pajak')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a><span class="separator">/</span>
    <span class="current">Pajak</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Master Pajak</h1><p class="page-subtitle">Kelola data pajak PPN, PPh, dan lainnya</p></div>
    <a href="{{ route('taxes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pajak</a>
</div>
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr><th>Kode</th><th>Nama</th><th>Tipe</th><th style="text-align:right;">Tarif (%)</th><th>Akun Penjualan</th><th>Akun Pembelian</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($taxes as $tax)
                <tr>
                    <td><span style="font-family:monospace;font-weight:600;">{{ $tax->code }}</span></td>
                    <td style="font-weight:500;">{{ $tax->name }}</td>
                    <td><span class="badge badge-{{ $tax->type==='ppn'?'primary':($tax->type==='pph'?'warning':'secondary') }}">{{ strtoupper($tax->type) }}</span></td>
                    <td style="text-align:right;font-weight:600;">{{ number_format($tax->rate,2) }}%</td>
                    <td style="font-size:12px;">{{ $tax->salesAccount?->name ?? '-' }}</td>
                    <td style="font-size:12px;">{{ $tax->purchaseAccount?->name ?? '-' }}</td>
                    <td>@if($tax->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-secondary">Non-Aktif</span>@endif</td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('taxes.edit',$tax) }}" class="btn btn-icon btn-secondary"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('taxes.destroy',$tax) }}" onsubmit="return confirm('Hapus pajak ini?')">@csrf @method('DELETE')
                                <button class="btn btn-icon btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:48px;color:#94a3b8;">Belum ada data pajak</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection