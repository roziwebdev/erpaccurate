{{-- resources/views/finance/accounts/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Bagan Akun')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Bagan Akun</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Bagan Akun (Chart of Accounts)</h1>
        <p class="page-subtitle">Kelola semua akun keuangan perusahaan</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('finance.accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Akun
        </a>
        <button class="btn btn-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>
</div>

<!-- Summary -->
<div class="grid grid-cols-4 gap-4 mb-6" style="grid-template-columns:repeat(5,1fr);">
    <div style="background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #e2e8f0;border-top:3px solid #4f46e5;">
        <div style="font-size:11px;color:#64748b;margin-bottom:4px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">ASET</div>
        <div style="font-size:16px;font-weight:700;">Rp {{ number_format($summary['asset'], 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #e2e8f0;border-top:3px solid #ef4444;">
        <div style="font-size:11px;color:#64748b;margin-bottom:4px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">KEWAJIBAN</div>
        <div style="font-size:16px;font-weight:700;">Rp {{ number_format($summary['liability'], 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #e2e8f0;border-top:3px solid #10b981;">
        <div style="font-size:11px;color:#64748b;margin-bottom:4px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">EKUITAS</div>
        <div style="font-size:16px;font-weight:700;">Rp {{ number_format($summary['equity'], 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #e2e8f0;border-top:3px solid #06b6d4;">
        <div style="font-size:11px;color:#64748b;margin-bottom:4px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">PENDAPATAN</div>
        <div style="font-size:16px;font-weight:700;">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #e2e8f0;border-top:3px solid #f59e0b;">
        <div style="font-size:11px;color:#64748b;margin-bottom:4px;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">BEBAN</div>
        <div style="font-size:16px;font-weight:700;">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari kode atau nama akun..." style="max-width:300px;">
            <select name="type" class="form-control" style="width:160px;">
                <option value="">Semua Tipe</option>
                <option value="asset" {{ request('type')=='asset'?'selected':'' }}>Aset</option>
                <option value="liability" {{ request('type')=='liability'?'selected':'' }}>Kewajiban</option>
                <option value="equity" {{ request('type')=='equity'?'selected':'' }}>Ekuitas</option>
                <option value="revenue" {{ request('type')=='revenue'?'selected':'' }}>Pendapatan</option>
                <option value="expense" {{ request('type')=='expense'?'selected':'' }}>Beban</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('finance.accounts.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Account Table -->
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Akun</th>
                    <th>Tipe</th>
                    <th>Sub Tipe</th>
                    <th style="text-align:right;">Saldo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentType = null;
                    $typeLabels = [
                        'asset' => ['label' => '1. ASET', 'color' => '#4f46e5'],
                        'liability' => ['label' => '2. KEWAJIBAN', 'color' => '#ef4444'],
                        'equity' => ['label' => '3. EKUITAS', 'color' => '#10b981'],
                        'revenue' => ['label' => '4. PENDAPATAN', 'color' => '#06b6d4'],
                        'expense' => ['label' => '5. BEBAN', 'color' => '#f59e0b'],
                    ];
                @endphp

                @forelse($accounts as $account)
                    @if($account->type !== $currentType)
                        @php $currentType = $account->type; @endphp
                        <tr>
                            <td colspan="7" style="background:{{ $typeLabels[$account->type]['color'] }}15;padding:10px 14px;border-bottom:1px solid #e2e8f0;">
                                <span style="font-weight:700;font-size:12px;color:{{ $typeLabels[$account->type]['color'] }};text-transform:uppercase;letter-spacing:1px;">
                                    {{ $typeLabels[$account->type]['label'] ?? $account->type }}
                                </span>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>
                            <span style="font-family:monospace;font-weight:{{ $account->is_header ? '700' : '400' }};padding-left:{{ ($account->level - 1) * 20 }}px;">
                                {{ $account->code }}
                            </span>
                        </td>
                        <td>
                            <span style="font-weight:{{ $account->is_header ? '700' : '400' }};color:{{ $account->is_header ? '#1e293b' : '#334155' }};">
                                {{ $account->name }}
                            </span>
                            @if($account->is_system)
                                <span style="font-size:10px;background:#ede9fe;color:#4f46e5;padding:2px 6px;border-radius:4px;margin-left:6px;">Sistem</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $account->type_color }}">{{ $account->type_label }}</span>
                        </td>
                        <td style="font-size:12px;color:#64748b;">{{ str_replace('_', ' ', ucfirst($account->sub_type ?? '-')) }}</td>
                        <td style="text-align:right;font-family:monospace;font-weight:{{ $account->is_header ? '700' : '500' }};">
                            @php
                                $balance = $account->current_balance;
                                $isNegative = $balance < 0;
                            @endphp
                            <span style="color:{{ $isNegative ? '#ef4444' : '#1e293b' }}">
                                {{ $isNegative ? '(' : '' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}{{ $isNegative ? ')' : '' }}
                            </span>
                        </td>
                        <td>
                            @if($account->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('finance.accounts.edit', $account) }}" class="btn btn-icon btn-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$account->is_system)
                                <form method="POST" action="{{ route('finance.accounts.destroy', $account) }}" onsubmit="return confirm('Hapus akun {{ addslashes($account->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;">
                            <i class="fas fa-list-ol" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                            Belum ada akun. Mulai tambahkan Chart of Accounts!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection