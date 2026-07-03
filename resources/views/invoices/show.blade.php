{{-- resources/views/sales/invoices/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Faktur - '.$invoice->number)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <a href="{{ route('sales.invoices.index') }}">Faktur Penjualan</a>
    <span class="separator">/</span>
    <span class="current">{{ $invoice->number }}</span>
@endsection

@section('content')
<!-- Header -->
<div class="page-header">
    <div class="flex items-center gap-3">
        <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="page-title">{{ $invoice->number }}</h1>
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                @if($invoice->isOverdue())
                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Jatuh Tempo {{ $invoice->due_date->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('sales.invoices.print',$invoice) }}" class="btn btn-secondary" target="_blank">
            <i class="fas fa-print"></i> Cetak
        </a>
        @if($invoice->status === 'draft')
        <a href="{{ route('sales.invoices.edit',$invoice) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit
        </a>
        <form method="POST" action="{{ route('sales.invoices.post',$invoice) }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Posting faktur ini? Jurnal akan dibuat otomatis.')">
                <i class="fas fa-paper-plane"></i> Posting Faktur
            </button>
        </form>
        @endif
        @if(in_array($invoice->status,['sent','partial']))
        <a href="{{ route('sales.receipts.create',['invoice_id'=>$invoice->id]) }}" class="btn btn-primary">
            <i class="fas fa-receipt"></i> Terima Pembayaran
        </a>
        @endif
        <div class="dropdown">
            <button class="btn btn-secondary" onclick="toggleDropdown('more-actions')">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu" id="more-actions">
                @if(!in_array($invoice->status,['paid','cancelled']))
                <form method="POST" action="{{ route('sales.invoices.cancel',$invoice) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="dropdown-item danger" onclick="return confirm('Batalkan faktur ini?')">
                        <i class="fas fa-ban"></i> Batalkan Faktur
                    </button>
                </form>
                @endif
                @if($invoice->status === 'draft')
                <form method="POST" action="{{ route('sales.invoices.destroy',$invoice) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="dropdown-item danger" onclick="return confirm('Hapus faktur ini? Tindakan tidak dapat diurungkan!')">
                        <i class="fas fa-trash"></i> Hapus Faktur
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6" style="grid-template-columns:2fr 1fr;">
    <!-- Left -->
    <div>
        <!-- Invoice Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div style="font-size:12px;color:#64748b;margin-bottom:4px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px;">Pelanggan</div>
                        <div style="font-weight:700;font-size:15px;">{{ $invoice->contact?->name }}</div>
                        @if($invoice->contact?->billing_address)
                        <div style="font-size:13px;color:#64748b;margin-top:4px;">{{ $invoice->contact->billing_address }}</div>
                        @endif
                        @if($invoice->contact?->phone)
                        <div style="font-size:13px;color:#64748b;"><i class="fas fa-phone"></i> {{ $invoice->contact->phone }}</div>
                        @endif
                        @if($invoice->contact?->npwp)
                        <div style="font-size:13px;color:#64748b;">NPWP: {{ $invoice->contact->npwp }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div style="font-size:12px;color:#64748b;margin-bottom:2px;font-weight:500;">Tanggal Faktur</div>
                                <div style="font-weight:600;">{{ $invoice->date->format('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size:12px;color:#64748b;margin-bottom:2px;font-weight:500;">Jatuh Tempo</div>
                                <div style="font-weight:600;color:{{ $invoice->isOverdue()?'#ef4444':'#1e293b' }};">{{ $invoice->due_date->format('d/m/Y') }}</div>
                            </div>
                            @if($invoice->reference)
                            <div>
                                <div style="font-size:12px;color:#64748b;margin-bottom:2px;font-weight:500;">Referensi</div>
                                <div style="font-weight:500;">{{ $invoice->reference }}</div>
                            </div>
                            @endif
                            @if($invoice->warehouse)
                            <div>
                                <div style="font-size:12px;color:#64748b;margin-bottom:2px;font-weight:500;">Gudang</div>
                                <div style="font-weight:500;">{{ $invoice->warehouse->name }}</div>
                            </div>
                            @endif
                            <div>
                                <div style="font-size:12px;color:#64748b;margin-bottom:2px;font-weight:500;">Dibuat Oleh</div>
                                <div style="font-size:13px;">{{ $invoice->createdBy?->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mb-4">
            <div class="card-header"><span class="card-title">Detail Item</span></div>
            <div style="overflow-x:auto;">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:center;">Satuan</th>
                            <th style="text-align:right;">Harga Satuan</th>
                            <th style="text-align:right;">Disc%</th>
                            <th style="text-align:right;">Pajak%</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                        <tr>
                            <td style="color:#94a3b8;">{{ $i+1 }}</td>
                            <td>
                                <div style="font-weight:500;">{{ $item->product?->name }}</div>
                                @if($item->description)<div style="font-size:12px;color:#94a3b8;">{{ $item->description }}</div>@endif
                            </td>
                            <td style="text-align:center;font-family:monospace;">{{ number_format($item->quantity,2) }}</td>
                            <td style="text-align:center;">{{ $item->unit?->symbol ?? $item->product?->unit?->symbol ?? '-' }}</td>
                            <td style="text-align:right;font-family:monospace;">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                            <td style="text-align:right;font-family:monospace;">{{ $item->discount_percent > 0 ? number_format($item->discount_percent,2).'%' : '-' }}</td>
                            <td style="text-align:right;font-family:monospace;">{{ $item->tax_percent > 0 ? number_format($item->tax_percent,0).'%' : '-' }}</td>
                            <td style="text-align:right;font-weight:600;font-family:monospace;">Rp {{ number_format($item->total,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($invoice->notes)
        <div class="card">
            <div class="card-body">
                <div style="font-size:12px;color:#64748b;margin-bottom:6px;font-weight:500;">CATATAN</div>
                <div style="font-size:14px;color:#334155;">{{ $invoice->notes }}</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Right: Summary -->
    <div>
        <!-- Payment Summary -->
        <div class="card mb-4">
            <div class="card-header"><span class="card-title">Ringkasan Pembayaran</span></div>
            <div class="card-body">
                <div style="font-size:13.5px;">
                    <div class="flex justify-between" style="margin-bottom:10px;">
                        <span style="color:#64748b;">Subtotal</span>
                        <span style="font-weight:500;font-family:monospace;">Rp {{ number_format($invoice->subtotal,0,',','.') }}</span>
                    </div>
                    @if($invoice->discount_amount > 0)
                    <div class="flex justify-between" style="margin-bottom:10px;">
                        <span style="color:#64748b;">Diskon</span>
                        <span style="color:#ef4444;font-family:monospace;">- Rp {{ number_format($invoice->discount_amount,0,',','.') }}</span>
                    </div>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <div class="flex justify-between" style="margin-bottom:10px;">
                        <span style="color:#64748b;">Pajak</span>
                        <span style="font-family:monospace;">Rp {{ number_format($invoice->tax_amount,0,',','.') }}</span>
                    </div>
                    @endif
                    @if($invoice->shipping_cost > 0)
                    <div class="flex justify-between" style="margin-bottom:10px;">
                        <span style="color:#64748b;">Ongkos Kirim</span>
                        <span style="font-family:monospace;">Rp {{ number_format($invoice->shipping_cost,0,',','.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between" style="padding:12px 0;border-top:2px solid #e2e8f0;border-bottom:1px solid #f1f5f9;margin:8px 0;">
                        <span style="font-weight:700;font-size:15px;">TOTAL</span>
                        <span style="font-weight:700;font-size:18px;color:#4f46e5;font-family:monospace;">Rp {{ number_format($invoice->total,0,',','.') }}</span>
                    </div>
                    <div class="flex justify-between" style="margin-bottom:8px;">
                        <span style="color:#64748b;">Terbayar</span>
                        <span style="font-weight:600;color:#10b981;font-family:monospace;">Rp {{ number_format($invoice->paid_amount,0,',','.') }}</span>
                    </div>
                    <div class="flex justify-between" style="padding:10px;background:{{ $invoice->remaining_amount>0?'#fee2e2':'#d1fae5' }};border-radius:10px;">
                        <span style="font-weight:700;color:{{ $invoice->remaining_amount>0?'#991b1b':'#065f46' }};">Sisa Tagihan</span>
                        <span style="font-weight:700;font-size:16px;color:{{ $invoice->remaining_amount>0?'#dc2626':'#059669' }};font-family:monospace;">
                            Rp {{ number_format($invoice->remaining_amount,0,',','.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        @if($invoice->total > 0)
        <div class="card mb-4">
            <div class="card-body">
                <div style="font-size:12px;color:#64748b;margin-bottom:8px;">Progress Pembayaran</div>
                @php $pct = min(($invoice->paid_amount/$invoice->total)*100,100); @endphp
                <div style="background:#f1f5f9;border-radius:8px;height:10px;overflow:hidden;">
                    <div style="background:linear-gradient(90deg,#10b981,#059669);height:100%;width:{{ $pct }}%;border-radius:8px;transition:width 0.5s;"></div>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:6px;text-align:right;">{{ number_format($pct,1) }}% terbayar</div>
            </div>
        </div>
        @endif

        <!-- Journal Info -->
        <div class="card">
            <div class="card-header"><span class="card-title">Info Akun</span></div>
            <div class="card-body">
                <div style="font-size:13px;">
                    <div style="margin-bottom:10px;">
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:3px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">Akun Piutang (AR)</div>
                        <div style="font-weight:500;">{{ $invoice->arAccount?->code }} - {{ $invoice->arAccount?->name }}</div>
                    </div>
                    <div style="margin-bottom:10px;">
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:3px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">Mata Uang</div>
                        <div style="font-weight:500;">{{ $invoice->currency_code }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:3px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">Dibuat</div>
                        <div style="font-size:12px;">{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection