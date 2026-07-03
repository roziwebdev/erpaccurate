{{-- resources/views/finance/journals/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Jurnal Umum</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Jurnal Umum</h1>
        <p class="page-subtitle">Daftar semua transaksi jurnal perusahaan</p>
    </div>
    <a href="{{ route('finance.journals.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Buat Jurnal
    </a>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3" style="flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nomor atau deskripsi..." style="flex:1;min-width:200px;">
            <select name="type" class="form-control" style="width:160px;">
                <option value="">Semua Tipe</option>
                <option value="general"   {{ request('type')=='general'?'selected':'' }}>Jurnal Umum</option>
                <option value="sales"     {{ request('type')=='sales'?'selected':'' }}>Penjualan</option>
                <option value="purchase"  {{ request('type')=='purchase'?'selected':'' }}>Pembelian</option>
                <option value="payment"   {{ request('type')=='payment'?'selected':'' }}>Pembayaran</option>
                <option value="receipt"   {{ request('type')=='receipt'?'selected':'' }}>Penerimaan</option>
                <option value="adjustment"{{ request('type')=='adjustment'?'selected':'' }}>Penyesuaian</option>
            </select>
            <select name="status" class="form-control" style="width:130px;">
                <option value="">Semua Status</option>
                <option value="draft"     {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                <option value="posted"    {{ request('status')=='posted'?'selected':'' }}>Diposting</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Dibatalkan</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:148px;">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="form-control" style="width:148px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('finance.journals.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>No. Jurnal</th>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Deskripsi</th>
                    <th>Referensi</th>
                    <th style="text-align:right;">Total Debit</th>
                    <th style="text-align:right;">Total Kredit</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journals as $journal)
                <tr>
                    <td>
                        <a href="{{ route('finance.journals.show',$journal) }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">{{ $journal->number }}</a>
                    </td>
                    <td>{{ $journal->date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-secondary" style="font-size:11px;">{{ $journal->type_label }}</span></td>
                    <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $journal->description }}</td>
                    <td style="font-size:12px;color:#94a3b8;">{{ $journal->reference ?? '-' }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:500;">Rp {{ number_format($journal->total_debit,0,',','.') }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:500;">Rp {{ number_format($journal->total_credit,0,',','.') }}</td>
                    <td style="font-size:12px;">{{ $journal->createdBy?->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $journal->status_color }}">{{ ucfirst($journal->status) }}</span></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('finance.journals.show',$journal) }}" class="btn btn-icon btn-secondary" title="Lihat"><i class="fas fa-eye"></i></a>
                            @if($journal->status === 'draft')
                            <a href="{{ route('finance.journals.edit',$journal) }}" class="btn btn-icon btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('finance.journals.post',$journal) }}">
                                @csrf
                                <button type="submit" class="btn btn-icon btn-success" title="Posting" onclick="return confirm('Posting jurnal ini?')"><i class="fas fa-paper-plane"></i></button>
                            </form>
                            @endif
                            <a href="{{ route('finance.journals.print',$journal) }}" class="btn btn-icon btn-secondary" target="_blank" title="Cetak"><i class="fas fa-print"></i></a>
                            @if($journal->status !== 'cancelled')
                            <form method="POST" action="{{ route('finance.journals.cancel',$journal) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-icon btn-danger" title="Batalkan" onclick="return confirm('Batalkan jurnal ini?')"><i class="fas fa-ban"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:48px;color:#94a3b8;">
                        <i class="fas fa-book" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                        Belum ada jurnal
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($journals->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:13px;color:#64748b;">Menampilkan {{ $journals->firstItem() }}–{{ $journals->lastItem() }} dari {{ $journals->total() }}</div>
        <div>{{ $journals->links() }}</div>
    </div>
    @endif
</div>
@endsection