<?php
// app/Http/Controllers/Finance/JournalController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $query = Journal::where('company_id',$companyId)->with(['createdBy'])->latest('date');

        if ($request->filled('type'))      $query->where('type',$request->type);
        if ($request->filled('status'))    $query->where('status',$request->status);
        if ($request->filled('date_from')) $query->whereDate('date','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('date','<=',$request->date_to);
        if ($request->filled('search'))    $query->where(fn($q)=>$q->where('number','like',"%{$request->search}%")->orWhere('description','like',"%{$request->search}%"));

        $journals = $query->paginate(20)->withQueryString();

        return view('finance.journals.index', compact('journals'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $accounts  = Account::where('company_id',$companyId)->where('is_active',true)->where('is_header',false)->orderBy('code')->get();
        $contacts  = Contact::where('company_id',$companyId)->where('is_active',true)->orderBy('name')->get();
        $nextNumber= 'JU/'.date('Y/m/').str_pad(Journal::where('company_id',$companyId)->whereMonth('created_at',now()->month)->count()+1,4,'0',STR_PAD_LEFT);

        return view('finance.journals.create', compact('accounts','contacts','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'description' => 'required|string',
            'entries'     => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:accounts,id',
            'entries.*.debit'      => 'required|numeric|min:0',
            'entries.*.credit'     => 'required|numeric|min:0',
        ]);

        $companyId   = auth()->user()->company_id;
        $totalDebit  = collect($request->entries)->sum(fn($e)=>floatval($e['debit']));
        $totalCredit = collect($request->entries)->sum(fn($e)=>floatval($e['credit']));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error','Jurnal tidak balance! Total debit dan kredit harus sama.');
        }

        DB::transaction(function() use ($request, $companyId, $totalDebit, $totalCredit) {
            $journal = Journal::create([
                'company_id'   => $companyId,
                'created_by'   => auth()->id(),
                'number'       => $request->number,
                'date'         => $request->date,
                'type'         => 'general',
                'description'  => $request->description,
                'reference'    => $request->reference,
                'status'       => 'draft',
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            foreach ($request->entries as $i => $entry) {
                JournalEntry::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $entry['account_id'],
                    'contact_id'  => $entry['contact_id'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'debit'       => floatval($entry['debit']),
                    'credit'      => floatval($entry['credit']),
                    'sort_order'  => $i,
                ]);
            }
        });

        return redirect()->route('finance.journals.index')->with('success','Jurnal berhasil disimpan!');
    }

    public function show(Journal $journal)
    {
        $journal->load(['entries.account','entries.contact','createdBy']);
        return view('finance.journals.show', compact('journal'));
    }

    public function post(Journal $journal)
    {
        if ($journal->status !== 'draft') {
            return back()->with('error','Jurnal sudah diposting atau dibatalkan!');
        }

        DB::transaction(function() use ($journal) {
            $journal->load('entries.account');

            // Update account balances
            foreach ($journal->entries as $entry) {
                $account = $entry->account;
                $isDebitNormal = in_array($account->type, ['asset','expense']);

                if ($isDebitNormal) {
                    $account->increment('current_balance', $entry->debit - $entry->credit);
                } else {
                    $account->increment('current_balance', $entry->credit - $entry->debit);
                }
            }

            $journal->update(['status' => 'posted']);
        });

        return back()->with('success','Jurnal berhasil diposting!');
    }

    public function cancel(Journal $journal)
    {
        if ($journal->status === 'posted') {
            DB::transaction(function() use ($journal) {
                $journal->load('entries.account');

                // Reverse account balances
                foreach ($journal->entries as $entry) {
                    $account = $entry->account;
                    $isDebitNormal = in_array($account->type, ['asset','expense']);

                    if ($isDebitNormal) {
                        $account->decrement('current_balance', $entry->debit - $entry->credit);
                    } else {
                        $account->decrement('current_balance', $entry->credit - $entry->debit);
                    }
                }

                $journal->update(['status' => 'cancelled']);
            });
        } else {
            $journal->update(['status' => 'cancelled']);
        }

        return back()->with('success','Jurnal berhasil dibatalkan!');
    }

    public function print(Journal $journal)
    {
        $journal->load(['entries.account','entries.contact','createdBy','company']);
        return view('finance.journals.print', compact('journal'));
    }

    public function edit(Journal $journal)
    {
        if ($journal->status !== 'draft') {
            return back()->with('error','Hanya jurnal draft yang bisa diedit!');
        }
        $companyId = auth()->user()->company_id;
        $accounts  = Account::where('company_id',$companyId)->where('is_active',true)->where('is_header',false)->orderBy('code')->get();
        $contacts  = Contact::where('company_id',$companyId)->where('is_active',true)->orderBy('name')->get();
        $journal->load('entries');

        return view('finance.journals.edit', compact('journal','accounts','contacts'));
    }

    public function update(Request $request, Journal $journal)
    {
        if ($journal->status !== 'draft') {
            return back()->with('error','Hanya jurnal draft yang bisa diedit!');
        }

        $request->validate([
            'date'        => 'required|date',
            'description' => 'required|string',
            'entries'     => 'required|array|min:2',
        ]);

        $totalDebit  = collect($request->entries)->sum(fn($e)=>floatval($e['debit']));
        $totalCredit = collect($request->entries)->sum(fn($e)=>floatval($e['credit']));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error','Jurnal tidak balance!');
        }

        DB::transaction(function() use ($request, $journal, $totalDebit, $totalCredit) {
            $journal->update([
                'date'         => $request->date,
                'description'  => $request->description,
                'reference'    => $request->reference,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            $journal->entries()->delete();

            foreach ($request->entries as $i => $entry) {
                JournalEntry::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $entry['account_id'],
                    'contact_id'  => $entry['contact_id'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'debit'       => floatval($entry['debit']),
                    'credit'      => floatval($entry['credit']),
                    'sort_order'  => $i,
                ]);
            }
        });

        return redirect()->route('finance.journals.show',$journal)->with('success','Jurnal berhasil diperbarui!');
    }
}