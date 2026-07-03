{{-- resources/views/sales/invoices/print.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur - {{ $invoice->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; }

        .invoice-wrapper { max-width: 800px; margin: 0 auto; padding: 30px; }

        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .company-logo { font-size: 22px; font-weight: 800; color: #4f46e5; }
        .company-info { font-size: 11px; color: #666; margin-top: 4px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 28px; font-weight: 800; color: #4f46e5; letter-spacing: 1px; }
        .invoice-number { font-size: 13px; font-weight: 600; color: #333; margin-top: 4px; }

        .invoice-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .meta-box { padding: 14px; background: #f8f9fa; border-radius: 8px; }
        .meta-label { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #888; letter-spacing: 1px; margin-bottom: 6px; }
        .meta-value { font-size: 12.5px; font-weight: 600; color: #1a1a1a; }
        .meta-sub { font-size: 11px; color: #666; margin-top: 2px; }

        .invoice-dates { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .date-item { display: flex; justify-content: space-between; font-size: 12px; }
        .date-item .label { color: #666; }
        .date-item .value { font-weight: 600; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #4f46e5; color: white; padding: 9px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.items td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; font-size: 12px; }
        table.items tr:nth-child(even) td { background: #fafafa; }

        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals-box { width: 280px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 12.5px; }
        .total-row.border-top { border-top: 1.5px solid #1a1a1a; margin-top: 6px; padding-top: 10px; }
        .total-row.grand { font-size: 14px; font-weight: 800; color: #4f46e5; }

        .footer-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 32px; }
        .signature-box { text-align: center; }
        .signature-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 6px; font-size: 11px; }

        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }

        .notes-box { background: #fef9ec; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px; font-size: 11.5px; margin-bottom: 20px; }
        .notes-box strong { display: block; margin-bottom: 4px; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div style="text-align:center;padding:15px;background:#f0f0f0;border-bottom:1px solid #ddd;" class="no-print">
        <button onclick="window.print()" style="background:#4f46e5;color:white;border:none;padding:10px 24px;border-radius:8px;cursor:pointer;font-size:14px;margin-right:8px;">
            <i>🖨</i> Cetak Faktur
        </button>
        <button onclick="window.close()" style="background:#f1f5f9;color:#333;border:1px solid #ddd;padding:10px 24px;border-radius:8px;cursor:pointer;font-size:14px;">
            ✕ Tutup
        </button>
    </div>

    <div class="invoice-wrapper">
        <!-- Header -->
        <div class="invoice-header">
            <div>
                @if($invoice->company->logo)
                <img src="{{ asset('storage/' . $invoice->company->logo) }}" height="50" alt="Logo">
                @else
                <div class="company-logo">{{ $invoice->company->name }}</div>
                @endif
                <div class="company-info">
                    {{ $invoice->company->address }}<br>
                    @if($invoice->company->phone)Tel: {{ $invoice->company->phone }} @endif
                    @if($invoice->company->npwp) | NPWP: {{ $invoice->company->npwp }} @endif
                </div>
            </div>
            <div class="invoice-title">
                <h1>FAKTUR</h1>
                <div class="invoice-number">{{ $invoice->number }}</div>
                <div style="margin-top:8px;">
                    @if($invoice->status === 'paid')
                        <span class="status-badge status-paid">✓ LUNAS</span>
                    @else
                        <span class="status-badge status-unpaid">BELUM LUNAS</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="invoice-meta">
            <div class="meta-box">
                <div class="meta-label">Tagihan Kepada</div>
                <div class="meta-value">{{ $invoice->contact->name }}</div>
                @if($invoice->contact->billing_address)
                <div class="meta-sub">{{ $invoice->contact->billing_address }}</div>
                @endif
                @if($invoice->contact->npwp)
                <div class="meta-sub">NPWP: {{ $invoice->contact->npwp }}</div>
                @endif
            </div>
            <div class="meta-box">
                <div class="meta-label">Informasi Faktur</div>
                <div class="total-row" style="padding:3px 0;">
                    <span class="label" style="color:#666;">Tanggal Faktur:</span>
                    <span class="value">{{ $invoice->date->format('d/m/Y') }}</span>
                </div>
                <div class="total-row" style="padding:3px 0;">
                    <span class="label" style="color:#666;">Jatuh Tempo:</span>
                    <span class="value" style="{{ $invoice->isOverdue() ? 'color:red;' : '' }}">{{ $invoice->due_date->format('d/m/Y') }}</span>
                </div>
                @if($invoice->reference)
                <div class="total-row" style="padding:3px 0;">
                    <span class="label" style="color:#666;">Referensi:</span>
                    <span class="value">{{ $invoice->reference }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th>Produk/Jasa</th>
                    <th style="width:60px;">Qty</th>
                    <th style="width:60px;">Satuan</th>
                    <th style="width:110px;text-align:right;">Harga Satuan</th>
                    <th style="width:60px;text-align:right;">Disc%</th>
                    <th style="width:60px;text-align:right;">Pajak%</th>
                    <th style="width:110px;text-align:right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name ?? '-' }}</strong>
                        @if($item->description)
                        <div style="font-size:10.5px;color:#666;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align:center;">{{ $item->unit?->symbol ?? $item->product?->unit?->symbol ?? '-' }}</td>
                    <td style="text-align:right;">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align:right;">{{ $item->discount_percent > 0 ? number_format($item->discount_percent, 1) . '%' : '-' }}</td>
                    <td style="text-align:right;">{{ $item->tax_percent > 0 ? number_format($item->tax_percent, 0) . '%' : '-' }}</td>
                    <td style="text-align:right;font-weight:600;">{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="total-row" style="color:#ef4444;">
                    <span>Diskon</span>
                    <span>- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="total-row">
                    <span>PPN (11%)</span>
                    <span>Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($invoice->shipping_cost > 0)
                <div class="total-row">
                    <span>Ongkos Kirim</span>
                    <span>Rp {{ number_format($invoice->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="total-row border-top grand">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                </div>
                @if($invoice->paid_amount > 0)
                <div class="total-row" style="color:#10b981;">
                    <span>Terbayar</span>
                    <span>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="total-row border-top" style="font-weight:700;color:{{ $invoice->remaining_amount > 0 ? '#ef4444' : '#10b981' }}">
                    <span>Sisa Tagihan</span>
                    <span>Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        @if($invoice->notes)
        <div class="notes-box">
            <strong>Catatan:</strong>
            {{ $invoice->notes }}
        </div>
        @endif

        <!-- Signature -->
        <div class="footer-section">
            <div>
                <div style="font-size:11px;color:#666;">Pembayaran dapat ditransfer ke:</div>
                <div style="font-size:12px;font-weight:600;margin-top:4px;">{{ $invoice->company->name }}</div>
                <div style="font-size:11.5px;margin-top:4px;">Hubungi kami untuk informasi rekening bank</div>
            </div>
            <div class="signature-box">
                <div style="font-size:11px;color:#666;">Hormat kami,</div>
                <div class="signature-line">{{ $invoice->company->name }}</div>
            </div>
        </div>

        <div style="margin-top:30px;text-align:center;font-size:10px;color:#aaa;border-top:1px solid #eee;padding-top:12px;">
            Faktur ini dibuat secara otomatis oleh sistem ERP • {{ $invoice->company->name }} • {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>