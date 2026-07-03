{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'ERP System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #e0e7ff;
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-active: #4f46e5;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
            scrollbar-width: thin;
            scrollbar-color: #1e293b transparent;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 2px; }

        .sidebar-logo {
            display: flex;
            align-items: center;
            padding: 20px 20px;
            border-bottom: 1px solid #1e293b;
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 800;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 18px;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.5px;
        }

        .sidebar-logo-text span {
            color: #4f46e5;
        }

        .sidebar-section {
            padding: 20px 0 8px;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #475569;
            padding: 0 20px 8px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-item:hover {
            color: #f1f5f9;
            background: #1e293b;
        }

        .sidebar-item.active {
            color: #fff;
            background: rgba(79, 70, 229, 0.2);
            border-left: 3px solid #4f46e5;
        }

        .sidebar-item .icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-item .badge {
            margin-left: auto;
            background: #4f46e5;
            color: white;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .sidebar-submenu {
            display: none;
            background: rgba(255,255,255,0.03);
        }

        .sidebar-submenu.show { display: block; }

        .sidebar-submenu .sidebar-item {
            padding-left: 48px;
            font-size: 13px;
        }

        .sidebar-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-toggle-btn .arrow {
            transition: transform 0.2s ease;
            font-size: 11px;
            color: #475569;
        }

        .sidebar-toggle-btn.open .arrow {
            transform: rotate(90deg);
        }

        /* ===== TOPBAR ===== */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 900;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: none;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            transition: all 0.2s;
        }

        .topbar-btn:hover { background: #f1f5f9; color: #334155; }

        .topbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .breadcrumb-nav {
            font-size: 13px;
            color: #64748b;
        }

        .breadcrumb-nav a { color: #64748b; text-decoration: none; }
        .breadcrumb-nav a:hover { color: #4f46e5; }
        .breadcrumb-nav .separator { margin: 0 8px; color: #cbd5e1; }
        .breadcrumb-nav .current { color: #1e293b; font-weight: 500; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 24px;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* ===== CARDS ===== */
        .card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .card-body { padding: 20px; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card.primary::before { background: linear-gradient(90deg, #4f46e5, #7c3aed); }
        .stat-card.success::before { background: linear-gradient(90deg, #10b981, #059669); }
        .stat-card.warning::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .stat-card.danger::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .stat-card.info::before { background: linear-gradient(90deg, #06b6d4, #0891b2); }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .stat-icon.primary { background: #ede9fe; color: #4f46e5; }
        .stat-icon.success { background: #d1fae5; color: #10b981; }
        .stat-icon.warning { background: #fef3c7; color: #f59e0b; }
        .stat-icon.danger { background: #fee2e2; color: #ef4444; }
        .stat-icon.info { background: #cffafe; color: #06b6d4; }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .stat-change {
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-change.up { color: #10b981; }
        .stat-change.down { color: #ef4444; }

        /* ===== TABLES ===== */
        .erp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .erp-table th {
            background: #f8fafc;
            padding: 10px 14px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .erp-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .erp-table tr:hover td { background: #f8fafc; }

        .erp-table tr:last-child td { border-bottom: none; }

        /* ===== BADGES ===== */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-primary { background: #ede9fe; color: #4f46e5; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .badge-info { background: #cffafe; color: #0e7490; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .badge-dark { background: #1e293b; color: #f1f5f9; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-primary { background: #4f46e5; color: white; }
        .btn-primary:hover { background: #3730a3; color: white; }

        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; color: white; }

        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; color: white; }

        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; color: white; }

        .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }

        .btn-outline-primary { background: transparent; color: #4f46e5; border: 1.5px solid #4f46e5; }
        .btn-outline-primary:hover { background: #4f46e5; color: white; }

        .btn-sm { padding: 6px 12px; font-size: 12.5px; border-radius: 8px; }
        .btn-lg { padding: 12px 24px; font-size: 15px; border-radius: 12px; }
        .btn-icon { padding: 8px; border-radius: 8px; width: 36px; height: 36px; justify-content: center; }

        /* ===== FORMS ===== */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background: #fff;
            transition: border-color 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control:disabled { background: #f8fafc; color: #94a3b8; }

        select.form-control { cursor: pointer; }
        textarea.form-control { resize: vertical; min-height: 80px; }

        .form-error {
            font-size: 12px;
            color: #ef4444;
            margin-top: 4px;
        }

        /* ===== INVOICE TABLE ===== */
        .invoice-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .invoice-items-table th {
            background: #f8fafc;
            padding: 10px 12px;
            font-weight: 600;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #e2e8f0;
        }

        .invoice-items-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .invoice-items-table input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        .invoice-items-table input:focus { border-color: #4f46e5; }

        .invoice-items-table select {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            outline: none;
            background: white;
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 13px;
            color: #64748b;
        }

        /* ===== DROPDOWN ===== */
        .dropdown { position: relative; display: inline-block; }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 4px);
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            min-width: 160px;
            z-index: 1000;
            padding: 6px;
            display: none;
        }

        .dropdown-menu.show { display: block; }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            font-size: 13.5px;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.15s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
        }

        .dropdown-item:hover { background: #f8fafc; }
        .dropdown-item.danger { color: #ef4444; }
        .dropdown-item.danger:hover { background: #fee2e2; }

        /* ===== ALERT ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .alert-info { background: #cffafe; color: #155e75; border: 1px solid #67e8f9; }

        /* ===== PAGINATION ===== */
        .pagination {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .pagination a, .pagination span {
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .pagination a:hover { background: #4f46e5; color: white; border-color: #4f46e5; }
        .pagination .active span { background: #4f46e5; color: white; border-color: #4f46e5; }

        /* ===== UTILS ===== */
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .gap-4 { gap: 16px; }
        .gap-6 { gap: 24px; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .mt-4 { margin-top: 16px; }
        .text-right { text-align: right; }
        .text-sm { font-size: 13px; }
        .text-xs { font-size: 11px; }
        .text-gray { color: #64748b; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .topbar { left: 0; }
            .main-content { margin-left: 0; }
            .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-cols-3 { grid-template-columns: 1fr; }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== PRINT STYLES ===== */
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            body { background: white; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <div class="sidebar-logo-icon">E</div>
            <div class="sidebar-logo-text">ERP <span>Accurate</span></div>
        </a>

        <!-- Dashboard -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-chart-pie"></i></span>
                Dashboard
            </a>
        </div>

        <!-- Sales -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Penjualan</div>
            <a href="#" class="sidebar-item sidebar-toggle-btn {{ request()->is('sales/*') ? 'open' : '' }}" onclick="toggleSubmenu('sales-menu')">
                <span class="icon"><i class="fas fa-shopping-cart"></i></span>
                Penjualan
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <div class="sidebar-submenu {{ request()->is('sales/*') ? 'show' : '' }}" id="sales-menu">
                <a href="{{ route('sales.orders.index') }}" class="sidebar-item {{ request()->routeIs('sales.orders.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                    Sales Order
                </a>
                <a href="{{ route('sales.invoices.index') }}" class="sidebar-item {{ request()->routeIs('sales.invoices.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-file-invoice"></i></span>
                    Faktur Penjualan
                </a>
                <a href="{{ route('sales.receipts.index') }}" class="sidebar-item {{ request()->routeIs('sales.receipts.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-receipt"></i></span>
                    Penerimaan Kas
                </a>
                <a href="{{ route('sales.delivery.index') }}" class="sidebar-item {{ request()->routeIs('sales.delivery.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-truck"></i></span>
                    Surat Jalan
                </a>
            </div>
        </div>

        <!-- Purchase -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Pembelian</div>
            <a href="#" class="sidebar-item sidebar-toggle-btn {{ request()->is('purchases/*') ? 'open' : '' }}" onclick="toggleSubmenu('purchase-menu')">
                <span class="icon"><i class="fas fa-shopping-bag"></i></span>
                Pembelian
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <div class="sidebar-submenu {{ request()->is('purchases/*') ? 'show' : '' }}" id="purchase-menu">
                <a href="{{ route('purchases.orders.index') }}" class="sidebar-item {{ request()->routeIs('purchases.orders.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                    Purchase Order
                </a>
                <a href="{{ route('purchases.invoices.index') }}" class="sidebar-item {{ request()->routeIs('purchases.invoices.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    Faktur Pembelian
                </a>
                <a href="{{ route('purchases.payments.index') }}" class="sidebar-item {{ request()->routeIs('purchases.payments.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                    Pembayaran Hutang
                </a>
                <a href="{{ route('purchases.receive.index') }}" class="sidebar-item {{ request()->routeIs('purchases.receive.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-boxes"></i></span>
                    Penerimaan Barang
                </a>
            </div>
        </div>

        <!-- Inventory -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Inventori</div>
            <a href="#" class="sidebar-item sidebar-toggle-btn {{ request()->is('inventory/*') ? 'open' : '' }}" onclick="toggleSubmenu('inventory-menu')">
                <span class="icon"><i class="fas fa-boxes"></i></span>
                Inventori
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <div class="sidebar-submenu {{ request()->is('inventory/*') ? 'show' : '' }}" id="inventory-menu">
                <a href="{{ route('inventory.products.index') }}" class="sidebar-item {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-box"></i></span>
                    Produk/Item
                </a>
                <a href="{{ route('inventory.warehouses.index') }}" class="sidebar-item {{ request()->routeIs('inventory.warehouses.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-warehouse"></i></span>
                    Gudang
                </a>
                <a href="{{ route('inventory.adjustments.index') }}" class="sidebar-item {{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-edit"></i></span>
                    Penyesuaian Stok
                </a>
                <a href="{{ route('inventory.transfers.index') }}" class="sidebar-item {{ request()->routeIs('inventory.transfers.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-exchange-alt"></i></span>
                    Transfer Antar Gudang
                </a>
            </div>
        </div>

        <!-- Finance -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Keuangan</div>
            <a href="#" class="sidebar-item sidebar-toggle-btn {{ request()->is('finance/*') ? 'open' : '' }}" onclick="toggleSubmenu('finance-menu')">
                <span class="icon"><i class="fas fa-landmark"></i></span>
                Keuangan
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <div class="sidebar-submenu {{ request()->is('finance/*') ? 'show' : '' }}" id="finance-menu">
                <a href="{{ route('finance.accounts.index') }}" class="sidebar-item {{ request()->routeIs('finance.accounts.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-list-ol"></i></span>
                    Bagan Akun
                </a>
                <a href="{{ route('finance.journals.index') }}" class="sidebar-item {{ request()->routeIs('finance.journals.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-book"></i></span>
                    Jurnal Umum
                </a>
                <a href="{{ route('finance.cashbank.index') }}" class="sidebar-item {{ request()->routeIs('finance.cashbank.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-university"></i></span>
                    Kas & Bank
                </a>
                <a href="{{ route('finance.assets.index') }}" class="sidebar-item {{ request()->routeIs('finance.assets.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-building"></i></span>
                    Aset Tetap
                </a>
            </div>
        </div>

        <!-- Reports -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Laporan</div>
            <a href="#" class="sidebar-item sidebar-toggle-btn {{ request()->is('reports/*') ? 'open' : '' }}" onclick="toggleSubmenu('reports-menu')">
                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                Laporan
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <div class="sidebar-submenu {{ request()->is('reports/*') ? 'show' : '' }}" id="reports-menu">
                <a href="{{ route('reports.profit-loss') }}" class="sidebar-item {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-balance-scale"></i></span>
                    Laba Rugi
                </a>
                <a href="{{ route('reports.balance-sheet') }}" class="sidebar-item {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-file-contract"></i></span>
                    Neraca
                </a>
                <a href="{{ route('reports.cash-flow') }}" class="sidebar-item {{ request()->routeIs('reports.cash-flow') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-water"></i></span>
                    Arus Kas
                </a>
                <a href="{{ route('reports.ledger') }}" class="sidebar-item {{ request()->routeIs('reports.ledger') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-scroll"></i></span>
                    Buku Besar
                </a>
                <a href="{{ route('reports.ar') }}" class="sidebar-item {{ request()->routeIs('reports.ar') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
                    Piutang Usaha
                </a>
                <a href="{{ route('reports.ap') }}" class="sidebar-item {{ request()->routeIs('reports.ap') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-hand-holding"></i></span>
                    Hutang Usaha
                </a>
                <a href="{{ route('reports.stock') }}" class="sidebar-item {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-cubes"></i></span>
                    Laporan Stok
                </a>
            </div>
        </div>

        <!-- Master Data -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Master Data</div>
            <a href="{{ route('contacts.index') }}" class="sidebar-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-address-book"></i></span>
                Pelanggan & Vendor
            </a>
            <a href="{{ route('taxes.index') }}" class="sidebar-item {{ request()->routeIs('taxes.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-percent"></i></span>
                Pajak
            </a>
        </div>

        <!-- Settings -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Pengaturan</div>
            <a href="{{ route('settings.company') }}" class="sidebar-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-cog"></i></span>
                Pengaturan
            </a>
            <a href="{{ route('settings.users') }}" class="sidebar-item">
                <span class="icon"><i class="fas fa-users"></i></span>
                Pengguna
            </a>
        </div>

        <div style="height: 24px;"></div>
    </aside>

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-btn" onclick="toggleSidebar()" style="margin-right:12px;">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="breadcrumb-nav">
                @yield('breadcrumb')
            </nav>
        </div>
        <div class="topbar-right">
            <!-- Notification -->
            <div class="dropdown">
                <button class="topbar-btn" onclick="toggleDropdown('notif-dropdown')">
                    <i class="fas fa-bell"></i>
                </button>
                <div class="dropdown-menu" id="notif-dropdown" style="min-width:300px;">
                    <div style="padding:10px 12px;font-weight:600;font-size:14px;border-bottom:1px solid #f1f5f9;margin-bottom:6px;">
                        Notifikasi
                    </div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-exclamation-circle" style="color:#f59e0b;"></i>
                        5 faktur akan jatuh tempo hari ini
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-box" style="color:#ef4444;"></i>
                        3 produk stok menipis
                    </a>
                </div>
            </div>

            <!-- User -->
            <div class="dropdown">
                <div class="topbar-avatar" onclick="toggleDropdown('user-dropdown')">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="dropdown-menu" id="user-dropdown">
                    <div style="padding:10px 12px;border-bottom:1px solid #f1f5f9;margin-bottom:6px;">
                        <div style="font-weight:600;font-size:13.5px;">{{ auth()->user()->name }}</div>
                        <div style="font-size:12px;color:#94a3b8;">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user"></i> Profil
                    </a>
                    <a href="{{ route('settings.company') }}" class="dropdown-item">
                        <i class="fas fa-cog"></i> Pengaturan
                    </a>
                    <div style="border-top:1px solid #f1f5f9;margin:6px 0;"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/numeral@2.0.6/numeral.min.js"></script>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // Submenu toggle
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById(menuId);
            const btn = submenu.previousElementSibling;
            submenu.classList.toggle('show');
            btn.classList.toggle('open');
        }

        // Dropdown toggle
        function toggleDropdown(dropdownId) {
            document.querySelectorAll('.dropdown-menu').forEach(d => {
                if (d.id !== dropdownId) d.classList.remove('show');
            });
            document.getElementById(dropdownId).classList.toggle('show');
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
            }
        });

        // Format currency
        function formatRupiah(amount) {
            return 'Rp ' + numeral(amount).format('0,0');
        }

        // Init Select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'classic',
                width: '100%'
            });

            // Init Flatpickr
            flatpickr('.datepicker', {
                dateFormat: 'Y-m-d',
                locale: 'id',
            });
        });
    </script>

    @stack('scripts')
</body>
</html>