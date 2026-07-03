<?php
// app/Http/Controllers/Reports/ReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $year      = $request->get('year', now()->year);
        $monthFrom = $request->get('month_from', 1);
        $monthTo   = $request->get('month_to', now()->month);

        $dateFrom = Carbon::create($year, $monthFrom, 1)->startOfMonth();
        $dateTo   = Carbon::create($year, $monthTo, 1)->endOfMonth();

        $revenues  = Account::where('company_id',$companyId)->where('type','revenue') ->where('is_header',false)->get();
        $expenses  = Account::where('company_id',$companyId)->where('type','expense') ->where('is_header',false)->get();

        $revenueData = [];
        foreach ($revenues as $acc) {
            $amount = JournalEntry::where('account_id',$acc->id)
                ->whereHas('journal', fn($q)=>$q->where('status','posted')->whereBetween('date',[$dateFrom,$dateTo]))
                ->selectRaw('SUM(credit) - SUM(debit) as net')->value('net') ?? 0;
            $revenueData[] = ['account'=>$acc,'amount'=>$amount];
        }

        $expenseData = [];
        foreach ($expenses as $acc) {
            $amount = JournalEntry::where('account_id',$acc->id)
                ->whereHas('journal', fn($q)=>$q->where('status','posted')->whereBetween('date',[$dateFrom,$dateTo]))
                ->selectRaw('SUM(debit) - SUM(credit) as net')->value('net') ?? 0;
            $expenseData[] = ['account'=>$acc,'amount'=>$amount];
        }

        $totalRevenue = collect($revenueData)->sum('amount');
        $totalExpense = collect($expenseData)->sum('amount');
        $netProfit    = $totalRevenue - $totalExpense;

        return view('reports.profit-loss', compact(
            'revenueData','expenseData','totalRevenue','totalExpense','netProfit',
            'year','monthFrom','monthTo','dateFrom','dateTo'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $date      = $request->get('date', now()->format('Y-m-d'));

        $assets      = Account::where('company_id',$companyId)->where('type','asset')    ->where('is_header',false)->where('is_active',true)->get();
        $liabilities = Account::where('company_id',$companyId)->where('type','liability')->where('is_header',false)->where('is_active',true)->get();
        $equities    = Account::where('company_id',$companyId)->where('type','equity')   ->where('is_header',false)->where('is_active',true)->get();

        $totalAssets      = $assets->sum('current_balance');
        $totalLiabilities = $liabilities->sum('current_balance');
        $totalEquities    = $equities->sum('current_balance');

        return view('reports.balance-sheet', compact(
            'assets','liabilities','equities',
            'totalAssets','totalLiabilities','totalEquities','date'
        ));
    }

    public function generalLedger(Request $request)
    {
        $companyId  = auth()->user()->company_id;
        $accountId  = $request->get('account_id');
        $dateFrom   = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo     = $request->get('date_to',   now()->endOfMonth()->format('Y-m-d'));

        $accounts = Account::where('company_id',$companyId)->where('is_active',true)->where('is_header',false)->orderBy('code')->get();
        $entries  = collect();
        $account  = null;
        $openingBalance = 0;

        if ($accountId) {
            $account = Account::findOrFail($accountId);
            $entries = JournalEntry::where('account_id',$accountId)
                ->whereHas('journal', fn($q)=>$q->where('status','posted')->whereBetween('date',[$dateFrom,$dateTo]))
                ->with(['journal','contact'])
                ->orderBy('created_at')
                ->get();

            // Opening balance before dateFrom
            $debitBefore  = JournalEntry::where('account_id',$accountId)->whereHas('journal',fn($q)=>$q->where('status','posted')->where('date','<',$dateFrom))->sum('debit');
            $creditBefore = JournalEntry::where('account_id',$accountId)->whereHas('journal',fn($q)=>$q->where('status','posted')->where('date','<',$dateFrom))->sum('credit');
            $openingBalance = in_array($account->type,['asset','expense'])
                ? ($account->opening_balance + $debitBefore - $creditBefore)
                : ($account->opening_balance + $creditBefore - $debitBefore);
        }

        return view('reports.ledger', compact('accounts','entries','account','dateFrom','dateTo','openingBalance'));
    }

    public function accountsReceivable(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $asOf      = $request->get('as_of', now()->format('Y-m-d'));

        $invoices = SalesInvoice::where('company_id',$companyId)
            ->whereIn('status',['sent','partial','overdue'])
            ->where('date','<=',$asOf)
            ->with('contact')
            ->orderBy('due_date')
            ->get();

        // Aging: current, 1-30, 31-60, 61-90, 90+
        $aging = ['current'=>0,'1_30'=>0,'31_60'=>0,'61_90'=>0,'over_90'=>0,'total'=>0];
        foreach ($invoices as $inv) {
            $days = now()->diffInDays($inv->due_date, false);
            $aging['total'] += $inv->remaining_amount;
            if     ($days >= 0)   $aging['current'] += $inv->remaining_amount;
            elseif ($days >= -30) $aging['1_30']    += $inv->remaining_amount;
            elseif ($days >= -60) $aging['31_60']   += $inv->remaining_amount;
            elseif ($days >= -90) $aging['61_90']   += $inv->remaining_amount;
            else                  $aging['over_90'] += $inv->remaining_amount;
        }

        return view('reports.ar', compact('invoices','aging','asOf'));
    }

    public function accountsPayable(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $asOf      = $request->get('as_of', now()->format('Y-m-d'));

        $invoices = PurchaseInvoice::where('company_id',$companyId)
            ->whereIn('status',['posted','partial','overdue'])
            ->where('date','<=',$asOf)
            ->with('contact')
            ->orderBy('due_date')
            ->get();

        $aging = ['current'=>0,'1_30'=>0,'31_60'=>0,'61_90'=>0,'over_90'=>0,'total'=>0];
        foreach ($invoices as $inv) {
            $days = now()->diffInDays($inv->due_date, false);
            $aging['total'] += $inv->remaining_amount;
            if     ($days >= 0)   $aging['current'] += $inv->remaining_amount;
            elseif ($days >= -30) $aging['1_30']    += $inv->remaining_amount;
            elseif ($days >= -60) $aging['31_60']   += $inv->remaining_amount;
            elseif ($days >= -90) $aging['61_90']   += $inv->remaining_amount;
            else                  $aging['over_90'] += $inv->remaining_amount;
        }

        return view('reports.ap', compact('invoices','aging','asOf'));
    }

    public function stockReport(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $products  = Product::where('company_id',$companyId)
            ->where('type','inventory')
            ->where('is_active',true)
            ->with(['category','unit','stocks.warehouse'])
            ->get();

        $totalValue = $products->sum(fn($p)=>$p->total_stock * $p->hpp);

        return view('reports.stock', compact('products','totalValue'));
    }

    public function trialBalance(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $dateFrom  = $request->get('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo    = $request->get('date_to',   now()->format('Y-m-d'));

        $accounts  = Account::where('company_id',$companyId)->where('is_header',false)->where('is_active',true)->orderBy('code')->get();
        $trialData = [];

        foreach ($accounts as $account) {
            $debit  = JournalEntry::where('account_id',$account->id)->whereHas('journal',fn($q)=>$q->where('status','posted')->whereBetween('date',[$dateFrom,$dateTo]))->sum('debit');
            $credit = JournalEntry::where('account_id',$account->id)->whereHas('journal',fn($q)=>$q->where('status','posted')->whereBetween('date',[$dateFrom,$dateTo]))->sum('credit');

            if ($debit > 0 || $credit > 0 || $account->opening_balance != 0) {
                $trialData[] = [
                    'account'         => $account,
                    'opening_balance' => $account->opening_balance,
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'ending_balance'  => $account->opening_balance + ($debit - $credit),
                ];
            }
        }

        $totalDebit  = collect($trialData)->sum('debit');
        $totalCredit = collect($trialData)->sum('credit');

        return view('reports.trial-balance', compact('trialData','totalDebit','totalCredit','dateFrom','dateTo'));
    }

    public function cashFlow(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $year      = $request->get('year', now()->year);

        // Operating Activities (from Sales & Purchases)
        $salesReceipts  = SalesInvoice::where('company_id',$companyId)->where('status','paid')->whereYear('date',$year)->sum('total');
        $purchasePayments= PurchaseInvoice::where('company_id',$companyId)->where('status','paid')->whereYear('date',$year)->sum('total');
        $operatingCash  = $salesReceipts - $purchasePayments;

        // Get cash/bank accounts
        $cashAccounts = Account::where('company_id',$companyId)->whereIn('sub_type',['cash','bank'])->where('is_header',false)->get();

        return view('reports.cash-flow', compact('salesReceipts','purchasePayments','operatingCash','cashAccounts','year'));
    }

    public function salesReport(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $dateFrom  = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->get('date_to',   now()->endOfMonth()->format('Y-m-d'));

        $invoices  = SalesInvoice::where('company_id',$companyId)
            ->whereIn('status',['sent','partial','paid'])
            ->whereBetween('date',[$dateFrom,$dateTo])
            ->with(['contact','items.product'])
            ->get();

        $totalSales    = $invoices->sum('total');
        $totalDiscount = $invoices->sum('discount_amount');
        $totalTax      = $invoices->sum('tax_amount');
        $totalPaid     = $invoices->sum('paid_amount');

        return view('reports.sales', compact('invoices','totalSales','totalDiscount','totalTax','totalPaid','dateFrom','dateTo'));
    }

    public function purchaseReport(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $dateFrom  = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->get('date_to',   now()->endOfMonth()->format('Y-m-d'));

        $invoices     = PurchaseInvoice::where('company_id',$companyId)
            ->whereIn('status',['posted','partial','paid'])
            ->whereBetween('date',[$dateFrom,$dateTo])
            ->with(['contact','items.product'])
            ->get();

        $totalPurchase = $invoices->sum('total');
        $totalPaid     = $invoices->sum('paid_amount');

        return view('reports.purchases', compact('invoices','totalPurchase','totalPaid','dateFrom','dateTo'));
    }

    public function export(Request $request, string $type)
    {
        // For full export, integrate maatwebsite/excel or barryvdh/laravel-dompdf
        return back()->with('info','Fitur export sedang dalam pengembangan.');
    }
}