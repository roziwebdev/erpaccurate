{{-- resources/views/sales/invoices/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Faktur Penjualan')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Faktur Penjualan</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Faktur Penjualan</h1>
        <p class="page-subtitle">Kelola semua faktur penjualan dan penagihan</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Faktur
        </a>
        <div class="dropdown">
            <button class="btn btn-secondary" onclick="toggleDropdown('export-menu')">
                <i class="fas fa-download"></i> Export
                <i class="fas fa-chevron-down" style="font-size:11px;"></i>
            </button>
            <div class="dropdown-menu" id="export-menu">
                <a href="#" class="dropdown-item"><i class="fas fa-file-excel"></i> Excel</a>
                <a href="#" class="dropdown-item"><i class="fas fa-file-pdf"></i> PDF</a>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;border-left:4px solid #94a3b8;">
        <div style="font-size:12px;color:#64748b;margin-bottom:4px;">DRAFT</div>
        <div style="font-size:18px;font-weight:700;">Rp {{ number_format($totalDraft, 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;border-left:4px solid #06b6d4;">
        <div style="font-size:12px;color:#64748b;margin-bottom:4px;">BELUM BAYAR</div>
        <div style="font-size:18px;font-weight:700;">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;border-left:4px solid #10b981;">
        <div style="font-size:12px;color:#64748b;margin-bottom:4px;">DIBAYAR BULAN INI</div>
        <div style="font-size:18px;font-weight:700;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;border-left:4px solid #ef4444;">
        <div style="font-size:12px;color:#64748b;margin-bottom:4px;">JATUH TEMPO</div>
        <div style="font-size:18px;font-weight:700;color:#ef4444;">Rp {{ number_format($totalOverdue, 0, ',', '.') }}</div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET" action="{{ route('sales.invoices.index') }}" class="flex items-center gap-3" style="flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nomor faktur atau pelanggan...">
            </div>
            <select name="status" class="form-control" style="width:160px;">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                <option value="sent" {{ request('status')=='sent'?'selected':'' }}>Terkirim</option>
                <option value="partial" {{ request('status')=='partial'?'selected':'' }}>Dibayar Sebagian</option>
                <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Lunas</option>
                <option value="overdue" {{ request('status')=='overdue'?'selected':'' }}>Jatuh Tempo</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Dibatalkan</option>
            </select>
            <select name="contact_id" class="form-control select2" style="width:200px;">
                <option value="">Semua Pelanggan</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ request('contact_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="width:150px;" placeholder="Dari tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" style="width:150px;" placeholder="Sampai tanggal">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                    <th>No. Faktur</th>
                    <th>Tanggal</th>
                    <th>Jatuh Tempo</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Terbayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><input type="checkbox" class="row-check" value="{{ $invoice->id }}"></td>
                    <td>
                        <a href="{{ route('sales.invoices.show', $invoice) }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">
                            {{ $invoice->number }}
                        </a>
                        @if($invoice->reference)
                        <div style="font-size:11px;color:#94a3b8;">Ref: {{ $invoice->reference }}</div>
                        @endif
                    </td>
                    <td>{{ $invoice->date->format('d/m/Y') }}</td>
                    <td>
                        {{ $invoice->due_date->format('d/m/Y') }}
                        @if($invoice->isOverdue())
                        <div style="font-size:11px;color:#ef4444;">{{ $invoice->due_date->diffForHumans() }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $invoice->contact->name ?? '-' }}</div>
                        @if($invoice->contact?->phone)
                        <div style="font-size:11px;color:#94a3b8;">{{ $invoice->contact->phone }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:600;font-family:monospace;">
                        Rp {{ number_format($invoice->total, 0, ',', '.') }}
                    </td>
                    <td style="text-align:right;font-family:monospace;color:#10b981;">
                        Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                    </td>
                    <td style="text-align:right;font-family:monospace;font-weight:600;color:{{ $invoice->remaining_amount > 0 ? '#ef4444' : '#10b981' }}">
                        Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('sales.invoices.show', $invoice) }}" class="btn btn-icon btn-secondary" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($invoice->status === 'draft')
                            <a href="{{ route('sales.invoices.edit', $invoice) }}" class="btn btn-icon btn-secondary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            <a href="{{ route('sales.invoices.print', $invoice) }}" class="btn btn-icon btn-secondary" target="_blank" title="Cetak">
                                <i class="fas fa-print"></i>
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-icon btn-secondary" onclick="toggleDropdown('action-{{ $invoice->id }}')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu" id="action-{{ $invoice->id }}">
                                    @if($invoice->status === 'draft')
                                    <form method="POST" action="{{ route('sales.invoices.post', $invoice) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-paper-plane"></i> Posting
                                        </button>
                                    </form>
                                    @endif
                                    @if(in_array($invoice->status, ['sent', 'partial']))
                                    <a href="{{ route('sales.receipts.create', ['invoice_id' => $invoice->id]) }}" class="dropdown-item">
                                        <i class="fas fa-receipt"></i> Terima Pembayaran
                                    </a>
                                    @endif
                                    @if(!in_array($invoice->status, ['paid', 'cancelled']))
                                    <form method="POST" action="{{ route('sales.invoices.cancel', $invoice) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item danger" onclick="return confirm('Yakin batalkan faktur ini?')">
                                            <i class="fas fa-ban"></i> Batalkan
                                        </button>
                                    </form>
                                    @endif
                                    @if($invoice->status === 'draft')
                                    <form method="POST" action="{{ route('sales.invoices.destroy', $invoice) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item danger" onclick="return confirm('Yakin hapus faktur ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:48px;">
                        <div style="color:#94a3b8;">
                            <i class="fas fa-file-invoice" style="font-size:40px;margin-bottom:12px;display:block;"></i>
                            Belum ada faktur penjualan
                        </div>
                        <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary" style="margin-top:12px;">
                            <i class="fas fa-plus"></i> Buat Faktur Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:between;">
        <div style="font-size:13px;color:#64748b;flex:1;">
            Menampilkan {{ $invoices->firstItem() }}-{{ $invoices->lastItem() }} dari {{ $invoices->total() }} faktur
        </div>
        <div class="pagination">
            {{ $invoices->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
}
</script>
@endpush