{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span class="current">Dashboard</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang, {{ auth()->user()->name }}! Berikut ringkasan bisnis Anda.</p>
    </div>
    <div class="flex items-center gap-4">
        <select class="form-control" style="width:auto;" id="periodSelect" onchange="changePeriod(this)">
            <option value="this_month">Bulan Ini</option>
            <option value="last_month">Bulan Lalu</option>
            <option value="this_year">Tahun Ini</option>
        </select>
        <span style="font-size:13px;color:#64748b;">{{ now()->format('d F Y') }}</span>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="stat-card primary">
        <div class="stat-icon primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value">{{ 'Rp ' . number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="stat-label">Total Penjualan</div>
        <div class="stat-change up">
            <i class="fas fa-arrow-up"></i> 12.5% dari bulan lalu
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon success">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-value">{{ 'Rp ' . number_format($totalAR, 0, ',', '.') }}</div>
        <div class="stat-label">Total Piutang</div>
        <div class="stat-change up">
            <i class="fas fa-arrow-up"></i> Belum terbayar
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon warning">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-value">{{ 'Rp ' . number_format($totalExpense, 0, ',', '.') }}</div>
        <div class="stat-label">Total Pembelian</div>
        <div class="stat-change down">
            <i class="fas fa-arrow-up"></i> Bulan ini
        </div>
    </div>

    <div class="stat-card danger">
        <div class="stat-icon danger">
            <i class="fas fa-hand-holding"></i>
        </div>
        <div class="stat-value">{{ 'Rp ' . number_format($totalAP, 0, ',', '.') }}</div>
        <div class="stat-label">Total Hutang</div>
        <div class="stat-change down">
            <i class="fas fa-arrow-down"></i> Belum terbayar
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card info">
        <div class="flex items-center justify-between">
            <div>
                <div class="stat-value" style="font-size:28px;">{{ $totalCustomers }}</div>
                <div class="stat-label">Total Pelanggan</div>
            </div>
            <div class="stat-icon info" style="margin-bottom:0;">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="stat-card primary">
        <div class="flex items-center justify-between">
            <div>
                <div class="stat-value" style="font-size:28px;">{{ $totalProducts }}</div>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-icon primary" style="margin-bottom:0;">
                <i class="fas fa-box"></i>
            </div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="flex items-center justify-between">
            <div>
                <div class="stat-value" style="font-size:28px;">{{ $totalInvoices }}</div>
                <div class="stat-label">Faktur Bulan Ini</div>
            </div>
            <div class="stat-icon success" style="margin-bottom:0;">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid gap-6 mb-6" style="grid-template-columns: 2fr 1fr;">
    <!-- Revenue Chart -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Grafik Penjualan vs Pembelian (6 Bulan)</span>
            <div class="flex items-center gap-3" style="font-size:12px;">
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:12px;height:12px;background:#4f46e5;border-radius:3px;display:inline-block;"></span>
                    Penjualan
                </span>
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:12px;height:12px;background:#f59e0b;border-radius:3px;display:inline-block;"></span>
                    Pembelian
                </span>
            </div>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Top 5 Pelanggan</span>
            <a href="{{ route('reports.ar') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding:0;">
            @foreach($topCustomers as $i => $item)
            @php
                $maxSales = $topCustomers->first()->total_sales;
                $percent = $maxSales > 0 ? ($item->total_sales / $maxSales) * 100 : 0;
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;">
                <div class="flex items-center justify-between mb-4" style="margin-bottom:6px;">
                    <div class="flex items-center gap-3">
                        <div style="width:28px;height:28px;background:{{ ['#ede9fe','#d1fae5','#fef3c7','#cffafe','#fee2e2'][$i] }};border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:{{ ['#4f46e5','#059669','#92400e','#0e7490','#dc2626'][$i] }};">
                            {{ $i + 1 }}
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:500;">{{ $item->contact->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div style="font-size:13px;font-weight:600;">{{ 'Rp ' . number_format($item->total_sales, 0, ',', '.') }}</div>
                </div>
                <div style="background:#f1f5f9;border-radius:4px;height:4px;">
                    <div style="background:#4f46e5;height:4px;border-radius:4px;width:{{ $percent }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Bottom Row -->
<div class="grid gap-6" style="grid-template-columns: 1fr 1fr;">
    <!-- Overdue Invoices -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;margin-right:6px;"></i>
                Faktur Jatuh Tempo
            </span>
            <a href="{{ route('sales.invoices.index', ['status'=>'overdue']) }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($overdueInvoices as $inv)
            <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:13.5px;font-weight:500;">{{ $inv->number }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ $inv->contact->name ?? '-' }} • Jatuh tempo: {{ $inv->due_date->format('d/m/Y') }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13.5px;font-weight:600;color:#ef4444;">{{ 'Rp ' . number_format($inv->remaining_amount, 0, ',', '.') }}</div>
                    <div style="font-size:11px;color:#ef4444;">{{ $inv->due_date->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:#94a3b8;">
                <i class="fas fa-check-circle" style="font-size:32px;margin-bottom:8px;color:#10b981;"></i>
                <div>Tidak ada faktur yang jatuh tempo</div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Low Stock -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <i class="fas fa-box-open" style="color:#f59e0b;margin-right:6px;"></i>
                Stok Menipis
            </span>
            <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($lowStockProducts as $product)
            <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:13.5px;font-weight:500;">{{ $product->name }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ $product->code }} • Min: {{ $product->min_stock }} {{ $product->unit?->symbol }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13.5px;font-weight:600;color:#f59e0b;">{{ $product->total_stock }} {{ $product->unit?->symbol }}</div>
                    <span class="badge badge-warning" style="font-size:10px;">Stok Rendah</span>
                </div>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:#94a3b8;">
                <i class="fas fa-check-circle" style="font-size:32px;margin-bottom:8px;color:#10b981;"></i>
                <div>Semua stok dalam kondisi normal</div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card mt-4" style="margin-top:24px;">
    <div class="card-header">
        <span class="card-title">Transaksi Terbaru</span>
        <a href="{{ route('finance.journals.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>No. Jurnal</th>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Deskripsi</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $journal)
                <tr>
                    <td>
                        <a href="{{ route('finance.journals.show', $journal) }}" style="color:#4f46e5;font-weight:500;text-decoration:none;">
                            {{ $journal->number }}
                        </a>
                    </td>
                    <td>{{ $journal->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge badge-secondary">{{ $journal->type_label }}</span>
                    </td>
                    <td style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $journal->description }}
                    </td>
                    <td class="text-right" style="font-family:monospace;">
                        {{ 'Rp ' . number_format($journal->total_debit, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="font-family:monospace;">
                        {{ 'Rp ' . number_format($journal->total_credit, 0, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $journal->status_color }}">{{ ucfirst($journal->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">
                        Belum ada transaksi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Revenue vs Expense Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($labels) !!},
        datasets: [
            {
                label: 'Penjualan',
                data: {!! json_encode($revenueChart) !!},
                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Pembelian',
                data: {!! json_encode($expenseChart) !!},
                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                borderRadius: 6,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f1f5f9' },
                ticks: {
                    callback: val => 'Rp ' + (val / 1000000).toFixed(0) + 'Jt',
                    font: { size: 11 }
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});
</script>
@endpush