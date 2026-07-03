{{-- resources/views/reports/ar.blade.php --}}
@extends('layouts.app')

@section('title', 'Laporan Piutang')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Laporan Piutang Usaha</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Laporan Piutang Usaha</h1>
        <p class="page-subtitle">Analisis aging piutang per {{ \Carbon\Carbon::parse($asOf)->format('d F Y') }}</p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Cetak</button>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3">
            <div>
                <label style="font-size:12px;font-weight:500;color:#64748b;display:block;margin-bottom:4px;">Per Tanggal</label>
                <input type="date" name="as_of" value="{{ $asOf }}" class="form-control datepicker" style="width:160px;">
            </div>
            <div style="align-self:flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Aging Summary -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">Ringkasan Aging Piutang</span></div>
    <div class="card-body">
        <div class="grid gap-4" style="grid-template-columns:repeat(5,1fr);">
            @php
                $agingLabels = ['current'=>'Belum Jatuh Tempo','1_30'=>'1 – 30 Hari','31_60'=>'31 – 60 Hari','61_90'=>'61 – 90 Hari','over_90'=>'> 90 Hari'];
                $agingColors = ['current'=>'#10b981','1_30'=>'#06b6d4','31_60'=>'#f59e0b','61_90'=>'#f97316','over_90'=>'#ef4444'];
            @endphp
            @foreach($agingLabels as $key=>$label)
            <div style="background:#f8fafc;border-radius:12px;padding:16px;border-top:4px solid {{ $agingColors[$key] }};text-align:center;">
                <div style="font-size:11px;color:#64748b;margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">{{ $label }}</div>
                <div style="font-size:18px;font-weight:700;color:{{ $agingColors[$key] }};">
                    Rp {{ number_format($aging[$key]/1000000,1) }}Jt
                </div>
                <div style="font-size:11.5px;color:#94a3b8;margin-top:4px;">
                    {{ $aging['total']>0 ? number_format(($aging[$key]/$aging['total'])*100,1).'%' : '0%' }}
                </div>
            </div>
            @endforeach
        </div>

        <!-- Progress Bar -->
        <div style="margin-top:16px;background:#f1f5f9;border-radius:8px;height:12px;overflow:hidden;display:flex;">
            @foreach($agingLabels as $key=>$label)
            @if($aging[$key] > 0 && $aging['total'] > 0)
            <div style="background:{{ $agingColors[$key] }};height:100%;width:{{ ($aging[$key]/$aging['total'])*100 }}%;transition:width 0.5s;" title="{{ $label }}: Rp {{ number_format($aging[$key],0,',','.') }}"></div>
            @endif
            @endforeach
        </div>

        <div style="margin-top:12px;text-align:right;font-weight:700;font-size:15px;">
            Total Piutang: <span style="color:#4f46e5;">Rp {{ number_format($aging['total'],0,',','.') }}</span>
        </div>
    </div>
</div>

<!-- Detail Table -->
<div class="card">
    <div class="card-header"><span class="card-title">Detail Piutang</span></div>
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Jatuh Tempo</th>
                    <th style="text-align:right;">Total</th>
                    <th style="text-align:right;">Terbayar</th>
                    <th style="text-align:right;">Sisa</th>
                    <th>Umur (Hari)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @forelse($invoices as $inv)
                @php
                    $days = now()->diffInDays($inv->due_date, false);
                    $grandTotal += $inv->remaining_amount;
                    $ageColor = $days >= 0 ? '#10b981' : ($days >= -30 ? '#06b6d4' : ($days >= -60 ? '#f59e0b' : ($days >= -90 ? '#f97316' : '#ef4444')));
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('sales.invoices.show',$inv) }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">{{ $inv->number }}</a>
                    </td>
                    <td style="font-weight:500;">{{ $inv->contact?->name ?? '-' }}</td>
                    <td>{{ $inv->date->format('d/m/Y') }}</td>
                    <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                    <td style="text-align:right;font-family:monospace;">Rp {{ number_format($inv->total,0,',','.') }}</td>
                    <td style="text-align:right;font-family:monospace;color:#10b981;">Rp {{ number_format($inv->paid_amount,0,',','.') }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:700;color:#ef4444;">Rp {{ number_format($inv->remaining_amount,0,',','.') }}</td>
                    <td>
                        @if($days >= 0)
                            <span style="color:#10b981;font-weight:600;font-size:12px;">Belum JT</span>
                        @else
                            <span style="color:{{ $ageColor }};font-weight:700;font-size:12px;">{{ abs($days) }} hari</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $inv->status_color }}">{{ $inv->status_label }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:48px;color:#94a3b8;">
                        <i class="fas fa-check-circle" style="font-size:40px;display:block;margin-bottom:12px;color:#10b981;"></i>
                        Tidak ada piutang outstanding!
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($invoices->count() > 0)
            <tfoot>
                <tr style="background:#1e293b;">
                    <td colspan="6" style="padding:14px;color:white;font-weight:700;text-transform:uppercase;">TOTAL PIUTANG OUTSTANDING</td>
                    <td style="padding:14px;text-align:right;color:#fbbf24;font-weight:800;font-size:16px;font-family:monospace;">Rp {{ number_format($grandTotal,0,',','.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection