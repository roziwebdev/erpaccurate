{{-- resources/views/reports/profit-loss.blade.php --}}
@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Laporan Laba Rugi</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Laporan Laba Rugi</h1>
        <p class="page-subtitle">Periode {{ \Carbon\Carbon::create($year,$monthFrom,1)->format('F') }} – {{ \Carbon\Carbon::create($year,$monthTo,1)->format('F Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Cetak</button>
        <a href="#" class="btn btn-secondary"><i class="fas fa-file-excel"></i> Export Excel</a>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3">
            <div>
                <label style="font-size:12px;font-weight:500;color:#64748b;display:block;margin-bottom:4px;">Tahun</label>
                <select name="year" class="form-control" style="width:100px;">
                    @for($y=now()->year;$y>=now()->year-5;$y--)
                    <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:500;color:#64748b;display:block;margin-bottom:4px;">Dari Bulan</label>
                <select name="month_from" class="form-control" style="width:130px;">
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i=>$m)
                    <option value="{{ $i+1 }}" {{ $monthFrom==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:500;color:#64748b;display:block;margin-bottom:4px;">Sampai Bulan</label>
                <select name="month_to" class="form-control" style="width:130px;">
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i=>$m)
                    <option value="{{ $i+1 }}" {{ $monthTo==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card success">
        <div class="stat-icon success"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-value">Rp {{ number_format($totalRevenue/1000000,2) }}Jt</div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon danger"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-value">Rp {{ number_format($totalExpense/1000000,2) }}Jt</div>
        <div class="stat-label">Total Beban</div>
    </div>
    <div class="stat-card {{ $netProfit >= 0 ? 'primary' : 'danger' }}">
        <div class="stat-icon {{ $netProfit >= 0 ? 'primary' : 'danger' }}">
            <i class="fas fa-{{ $netProfit >= 0 ? 'chart-line' : 'chart-line fa-rotate-180' }}"></i>
        </div>
        <div class="stat-value" style="color:{{ $netProfit>=0?'#10b981':'#ef4444' }}">
            Rp {{ number_format(abs($netProfit)/1000000,2) }}Jt
        </div>
        <div class="stat-label">{{ $netProfit >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}</div>
    </div>
</div>

<!-- Report -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Laporan Laba Rugi</span>
        <span style="font-size:13px;color:#64748b;">{{ auth()->user()->company->name }}</span>
    </div>
    <div class="card-body" style="padding:0;">
        <!-- Header -->
        <div style="padding:20px 24px;text-align:center;border-bottom:2px solid #e2e8f0;">
            <div style="font-size:18px;font-weight:700;">{{ auth()->user()->company->name }}</div>
            <div style="font-size:15px;font-weight:600;margin-top:4px;">LAPORAN LABA RUGI</div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">
                Periode {{ $dateFrom->format('d F Y') }} s/d {{ $dateTo->format('d F Y') }}
            </div>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
            <!-- PENDAPATAN -->
            <thead>
                <tr style="background:#d1fae5;">
                    <th colspan="2" style="padding:12px 24px;text-align:left;font-size:13px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:0.5px;">
                        <i class="fas fa-arrow-up"></i> PENDAPATAN
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueData as $item)
                @if($item['amount'] != 0)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:10px 24px;padding-left:40px;">{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                    <td style="padding:10px 24px;text-align:right;font-family:monospace;font-weight:500;">
                        Rp {{ number_format($item['amount'],0,',','.') }}
                    </td>
                </tr>
                @endif
                @endforeach
                <tr style="background:#f0fdf4;border-top:1px solid #a7f3d0;">
                    <td style="padding:12px 24px;font-weight:700;color:#065f46;">Total Pendapatan</td>
                    <td style="padding:12px 24px;text-align:right;font-weight:700;color:#065f46;font-family:monospace;font-size:15px;">
                        Rp {{ number_format($totalRevenue,0,',','.') }}
                    </td>
                </tr>
            </tbody>

            <!-- BEBAN -->
            <thead>
                <tr style="background:#fee2e2;">
                    <th colspan="2" style="padding:12px 24px;text-align:left;font-size:13px;font-weight:700;color:#991b1b;text-transform:uppercase;letter-spacing:0.5px;">
                        <i class="fas fa-arrow-down"></i> BEBAN
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenseData as $item)
                @if($item['amount'] != 0)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:10px 24px;padding-left:40px;">{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                    <td style="padding:10px 24px;text-align:right;font-family:monospace;font-weight:500;color:#ef4444;">
                        (Rp {{ number_format($item['amount'],0,',','.') }})
                    </td>
                </tr>
                @endif
                @endforeach
                <tr style="background:#fff5f5;border-top:1px solid #fca5a5;">
                    <td style="padding:12px 24px;font-weight:700;color:#991b1b;">Total Beban</td>
                    <td style="padding:12px 24px;text-align:right;font-weight:700;color:#991b1b;font-family:monospace;font-size:15px;">
                        (Rp {{ number_format($totalExpense,0,',','.') }})
                    </td>
                </tr>
            </tbody>

            <!-- NET PROFIT -->
            <tfoot>
                <tr style="background:{{ $netProfit>=0?'#4f46e5':'#dc2626' }};">
                    <td style="padding:16px 24px;font-weight:800;font-size:15px;color:white;text-transform:uppercase;letter-spacing:0.5px;">
                        {{ $netProfit >= 0 ? '✓ LABA BERSIH' : '⚠ RUGI BERSIH' }}
                    </td>
                    <td style="padding:16px 24px;text-align:right;font-weight:800;font-size:18px;color:white;font-family:monospace;">
                        {{ $netProfit < 0 ? '(' : '' }}Rp {{ number_format(abs($netProfit),0,',','.') }}{{ $netProfit < 0 ? ')' : '' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:10px 24px;font-size:12px;color:#94a3b8;text-align:center;">
                        Margin Keuntungan: {{ $totalRevenue > 0 ? number_format(($netProfit/$totalRevenue)*100,2) : 0 }}%
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection