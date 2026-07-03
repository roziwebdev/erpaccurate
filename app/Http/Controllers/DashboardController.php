<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Journal;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        // Summary Cards
        $totalRevenue = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial', 'paid'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');

        $totalExpense = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['posted', 'partial', 'paid'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');

        $totalAR = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('remaining_amount');

        $totalAP = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['posted', 'partial', 'overdue'])
            ->sum('remaining_amount');

        // Overdue Invoices
        $overdueInvoices = SalesInvoice::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereIn('status', ['sent', 'partial'])
            ->with('contact')
            ->latest('due_date')
            ->limit(5)
            ->get();

        // Recent Transactions
        $recentTransactions = Journal::where('company_id', $companyId)
            ->where('status', 'posted')
            ->with('createdBy')
            ->latest()
            ->limit(10)
            ->get();

        // Monthly Revenue Chart Data (last 6 months)
        $revenueChart = [];
        $expenseChart = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $revenueChart[] = SalesInvoice::where('company_id', $companyId)
                ->whereIn('status', ['sent', 'partial', 'paid'])
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('total');

            $expenseChart[] = PurchaseInvoice::where('company_id', $companyId)
                ->whereIn('status', ['posted', 'partial', 'paid'])
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('total');
        }

        // Top 5 Customers
        $topCustomers = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial', 'paid'])
            ->whereYear('date', now()->year)
            ->selectRaw('contact_id, SUM(total) as total_sales')
            ->groupBy('contact_id')
            ->with('contact')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        // Low Stock Products
        $lowStockProducts = Product::where('company_id', $companyId)
            ->where('type', 'inventory')
            ->where('track_inventory', true)
            ->where('is_active', true)
            ->with(['stocks', 'unit'])
            ->get()
            ->filter(fn ($p) => $p->total_stock <= $p->min_stock && $p->min_stock > 0)
            ->take(5);

        // Stats
        $totalCustomers = Contact::where('company_id', $companyId)
            ->whereIn('type', ['customer', 'both'])
            ->where('is_active', true)
            ->count();

        $totalProducts = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        $totalInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereMonth('date', now()->month)
            ->count();

        return view('dashboard.index', compact(
            'totalRevenue', 'totalExpense', 'totalAR', 'totalAP',
            'overdueInvoices', 'recentTransactions',
            'revenueChart', 'expenseChart', 'labels',
            'topCustomers', 'lowStockProducts',
            'totalCustomers', 'totalProducts', 'totalInvoices'
        ));
    }
}