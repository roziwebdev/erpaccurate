<?php
// app/Http/Controllers/Finance/AccountController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Account::where('company_id', $companyId)
            ->with(['parent', 'children'])
            ->orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        $accounts = $query->get();

        // Group by type for summary
        $summary = [
            'asset' => $accounts->where('type', 'asset')->where('is_header', false)->sum('current_balance'),
            'liability' => $accounts->where('type', 'liability')->where('is_header', false)->sum('current_balance'),
            'equity' => $accounts->where('type', 'equity')->where('is_header', false)->sum('current_balance'),
            'revenue' => $accounts->where('type', 'revenue')->where('is_header', false)->sum('current_balance'),
            'expense' => $accounts->where('type', 'expense')->where('is_header', false)->sum('current_balance'),
        ];

        $categories = AccountCategory::all();

        return view('finance.accounts.index', compact('accounts', 'summary', 'categories'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $parentAccounts = Account::where('company_id', $companyId)
            ->where('is_header', true)
            ->orderBy('code')
            ->get();

        $categories = AccountCategory::all();

        return view('finance.accounts.create', compact('parentAccounts', 'categories'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'code' => "required|string|unique:accounts,code,NULL,id,company_id,{$companyId}",
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_category_id' => 'required|exists:account_categories,id',
        ]);

        $level = 1;
        if ($request->parent_id) {
            $parent = Account::findOrFail($request->parent_id);
            $level = $parent->level + 1;
        }

        Account::create([
            'company_id' => $companyId,
            'account_category_id' => $request->account_category_id,
            'parent_id' => $request->parent_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'sub_type' => $request->sub_type,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
            'is_header' => $request->boolean('is_header'),
            'is_active' => $request->boolean('is_active', true),
            'level' => $level,
        ]);

        return redirect()->route('finance.accounts.index')
            ->with('success', 'Akun berhasil ditambahkan!');
    }

    public function edit(Account $account)
    {
        $companyId = auth()->user()->company_id;

        $parentAccounts = Account::where('company_id', $companyId)
            ->where('is_header', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        $categories = AccountCategory::all();

        return view('finance.accounts.edit', compact('account', 'parentAccounts', 'categories'));
    }

    public function update(Request $request, Account $account)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'code' => "required|string|unique:accounts,code,{$account->id},id,company_id,{$companyId}",
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
        ]);

        $account->update([
            'account_category_id' => $request->account_category_id,
            'parent_id' => $request->parent_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'sub_type' => $request->sub_type,
            'is_header' => $request->boolean('is_header'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('finance.accounts.index')
            ->with('success', 'Akun berhasil diperbarui!');
    }

    public function destroy(Account $account)
    {
        if ($account->is_system) {
            return redirect()->back()->with('error', 'Akun sistem tidak dapat dihapus!');
        }

        if ($account->journalEntries()->exists()) {
            return redirect()->back()->with('error', 'Akun sudah memiliki transaksi dan tidak dapat dihapus!');
        }

        $account->delete();

        return redirect()->route('finance.accounts.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}