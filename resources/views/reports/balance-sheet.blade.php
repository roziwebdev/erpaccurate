{{-- resources/views/reports/balance-sheet.blade.php --}}
@extends('layouts.app')

@section('title', 'Neraca')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Neraca</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Neraca (Balance Sheet)</h1>
        <p class="page-subtitle">Per tanggal {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Cetak</button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3">
            <div>
                <label style="font-size:12px;font-weight:500;color:#64748b;display:block;margin-bottom:4px;">Per Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control datepicker" style="width:160px;">
            </div>
            <div style="align-self:flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card primary">
        <div class="stat-icon primary"><i class="fas fa-building"></i></div>
        <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalAssets/1000000,1) }}Jt</div>
        <div class="stat-label">Total Aset</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon danger"><i class="fas fa-hand-holding"></i></div>
        <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalLiabilities/1000000,1) }}Jt</div>
        <div class="stat-label">Total Kewajiban</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon success"><i class="fas fa-balance-scale"></i></div>
        <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalEquities/1000000,1) }}Jt</div>
        <div class="stat-label">Total Ekuitas</div>
    </div>
</div>

<!-- Balance Check -->
@php $liabEquity = $totalLiabilities + $totalEquities; $diff = abs($totalAssets - $liabEquity); @endphp
@if($diff < 1)
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Neraca seimbang! Aset = Kewajiban + Ekuitas</div>
@else
<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Neraca tidak seimbang! Selisih: Rp {{ number_format($diff,0,',','.') }}</div>
@endif

<!-- Balance Sheet Table -->
<div class="grid gap-6" style="grid-template-columns:1fr 1fr;">
    <!-- Left: Assets -->
    <div class="card">
        <div style="background:#4f46e5;padding:14px 20px;border-radius:16px 16px 0 0;">
            <span style="font-weight:700;color:white;font-size:14px;text-transform:uppercase;letter-spacing:0.5px;">ASET</span>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            @php
                $currentSubTypes = ['cash','bank','receivable','inventory','other_asset'];
                $nonCurrentSubTypes = ['fixed_asset'];
                $currentAssets = $assets->whereIn('sub_type', $currentSubTypes);
                $nonCurrentAssets = $assets->whereIn('sub_type', $nonCurrentSubTypes);
            @endphp

            <tr style="background:#ede9fe;">
                <td colspan="2" style="padding:10px 16px;font-weight:700;font-size:12px;color:#4f46e5;text-transform:uppercase;">Aset Lancar</td>
            </tr>
            @foreach($currentAssets as $acc)
            @if($acc->current_balance != 0)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:9px 16px;padding-left:28px;font-size:13px;">{{ $acc->name }}</td>
                <td style="padding:9px 16px;text-align:right;font-family:monospace;">
                    @php $bal = $acc->current_balance; @endphp
                    @if($bal < 0)
                    <span style="color:#ef4444;">({{ number_format(abs($bal),0,',','.') }})</span>
                    @else
                    {{ number_format($bal,0,',','.') }}
                    @endif
                </td>
            </tr>
            @endif
            @endforeach
            <tr style="background:#f5f3ff;">
                <td style="padding:10px 16px;font-weight:600;font-size:13px;">Total Aset Lancar</td>
                <td style="padding:10px 16px;text-align:right;font-weight:700;font-family:monospace;">{{ number_format($currentAssets->sum('current_balance'),0,',','.') }}</td>
            </tr>

            <tr style="background:#ede9fe;">
                <td colspan="2" style="padding:10px 16px;font-weight:700;font-size:12px;color:#4f46e5;text-transform:uppercase;">Aset Tidak Lancar</td>
            </tr>
            @foreach($nonCurrentAssets as $acc)
            @if($acc->current_balance != 0)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:9px 16px;padding-left:28px;font-size:13px;">{{ $acc->name }}</td>
                <td style="padding:9px 16px;text-align:right;font-family:monospace;">
                    @php $bal = $acc->current_balance; @endphp
                    @if($bal < 0)
                    <span style="color:#ef4444;">({{ number_format(abs($bal),0,',','.') }})</span>
                    @else
                    {{ number_format($bal,0,',','.') }}
                    @endif
                </td>
            </tr>
            @endif
            @endforeach
            <tr style="background:#f5f3ff;">
                <td style="padding:10px 16px;font-weight:600;font-size:13px;">Total Aset Tidak Lancar</td>
                <td style="padding:10px 16px;text-align:right;font-weight:700;font-family:monospace;">{{ number_format($nonCurrentAssets->sum('current_balance'),0,',','.') }}</td>
            </tr>

            <tr style="background:#4f46e5;">
                <td style="padding:14px 16px;font-weight:800;font-size:14px;color:white;text-transform:uppercase;">TOTAL ASET</td>
                <td style="padding:14px 16px;text-align:right;font-weight:800;font-size:16px;color:white;font-family:monospace;">{{ number_format($totalAssets,0,',','.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Right: Liabilities + Equity -->
    <div>
        <!-- Liabilities -->
        <div class="card mb-4">
            <div style="background:#ef4444;padding:14px 20px;border-radius:16px 16px 0 0;">
                <span style="font-weight:700;color:white;font-size:14px;text-transform:uppercase;letter-spacing:0.5px;">KEWAJIBAN</span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                @php
                    $currentLiab    = $liabilities->whereIn('sub_type',['payable','short_term_loan','other_liability']);
                    $nonCurrentLiab = $liabilities->where('sub_type','long_term_loan');
                @endphp
                <tr style="background:#fee2e2;">
                    <td colspan="2" style="padding:10px 16px;font-weight:700;font-size:12px;color:#991b1b;text-transform:uppercase;">Kewajiban Lancar</td>
                </tr>
                @foreach($currentLiab as $acc)
                @if($acc->current_balance != 0)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:9px 16px;padding-left:28px;">{{ $acc->name }}</td>
                    <td style="padding:9px 16px;text-align:right;font-family:monospace;">{{ number_format($acc->current_balance,0,',','.') }}</td>
                </tr>
                @endif
                @endforeach
                <tr style="background:#fef2f2;">
                    <td style="padding:10px 16px;font-weight:600;">Total Kewajiban Lancar</td>
                    <td style="padding:10px 16px;text-align:right;font-weight:700;font-family:monospace;">{{ number_format($currentLiab->sum('current_balance'),0,',','.') }}</td>
                </tr>
                <tr style="background:#fee2e2;">
                    <td colspan="2" style="padding:10px 16px;font-weight:700;font-size:12px;color:#991b1b;text-transform:uppercase;">Kewajiban Tidak Lancar</td>
                </tr>
                @foreach($nonCurrentLiab as $acc)
                @if($acc->current_balance != 0)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:9px 16px;padding-left:28px;">{{ $acc->name }}</td>
                    <td style="padding:9px 16px;text-align:right;font-family:monospace;">{{ number_format($acc->current_balance,0,',','.') }}</td>
                </tr>
                @endif
                @endforeach
                <tr style="background:#ef4444;">
                    <td style="padding:12px 16px;font-weight:800;color:white;text-transform:uppercase;font-size:13px;">TOTAL KEWAJIBAN</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:white;font-family:monospace;font-size:15px;">{{ number_format($totalLiabilities,0,',','.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Equity -->
        <div class="card">
            <div style="background:#10b981;padding:14px 20px;border-radius:16px 16px 0 0;">
                <span style="font-weight:700;color:white;font-size:14px;text-transform:uppercase;letter-spacing:0.5px;">EKUITAS</span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                @foreach($equities as $acc)
                @if($acc->current_balance != 0)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:9px 16px;padding-left:28px;">{{ $acc->name }}</td>
                    <td style="padding:9px 16px;text-align:right;font-family:monospace;">{{ number_format($acc->current_balance,0,',','.') }}</td>
                </tr>
                @endif
                @endforeach
                <tr style="background:#10b981;">
                    <td style="padding:12px 16px;font-weight:800;color:white;text-transform:uppercase;font-size:13px;">TOTAL EKUITAS</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:white;font-family:monospace;font-size:15px;">{{ number_format($totalEquities,0,',','.') }}</td>
                </tr>
                <tr style="background:#1e293b;">
                    <td style="padding:14px 16px;font-weight:800;color:white;text-transform:uppercase;font-size:14px;">TOTAL KEWAJIBAN + EKUITAS</td>
                    <td style="padding:14px 16px;text-align:right;font-weight:800;color:white;font-family:monospace;font-size:16px;">{{ number_format($liabEquity,0,',','.') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection