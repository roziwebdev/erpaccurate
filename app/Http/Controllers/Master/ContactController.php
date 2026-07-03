<?php
// app/Http/Controllers/Master/ContactController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\ContactGroup;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $query = Contact::where('company_id', $companyId)->with(['group'])->latest();

        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name','like',"%{$request->search}%")
                  ->orWhere('code','like',"%{$request->search}%")
                  ->orWhere('email','like',"%{$request->search}%")
                  ->orWhere('phone','like',"%{$request->search}%");
            });
        }

        $contacts  = $query->paginate(15)->withQueryString();
        $groups    = ContactGroup::where('company_id', $companyId)->get();
        $totalCust = Contact::where('company_id',$companyId)->whereIn('type',['customer','both'])->count();
        $totalVend = Contact::where('company_id',$companyId)->whereIn('type',['vendor','both'])->count();

        return view('master.contacts.index', compact('contacts','groups','totalCust','totalVend'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $groups    = ContactGroup::where('company_id',$companyId)->get();
        $arAccounts= Account::where('company_id',$companyId)->where('sub_type','receivable')->where('is_active',true)->get();
        $apAccounts= Account::where('company_id',$companyId)->where('sub_type','payable')->where('is_active',true)->get();
        $nextCodeCust = $this->generateCode($companyId,'customer');
        $nextCodeVend = $this->generateCode($companyId,'vendor');

        return view('master.contacts.create', compact('groups','arAccounts','apAccounts','nextCodeCust','nextCodeVend'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:customer,vendor,both,employee',
            'email' => 'nullable|email',
            'code'  => "required|unique:contacts,code,NULL,id,company_id,{$companyId}",
        ]);

        Contact::create(array_merge($request->except('_token'), ['company_id'=>$companyId]));

        return redirect()->route('contacts.index')->with('success','Kontak berhasil ditambahkan!');
    }

    public function show(Contact $contact)
    {
        $contact->load(['group','salesInvoices','purchaseInvoices']);
        $totalSales    = $contact->salesInvoices()->whereIn('status',['sent','partial','paid'])->sum('total');
        $totalPurchase = $contact->purchaseInvoices()->whereIn('status',['posted','partial','paid'])->sum('total');
        $outstandingAR = $contact->salesInvoices()->whereIn('status',['sent','partial','overdue'])->sum('remaining_amount');
        $outstandingAP = $contact->purchaseInvoices()->whereIn('status',['posted','partial','overdue'])->sum('remaining_amount');

        return view('master.contacts.show', compact('contact','totalSales','totalPurchase','outstandingAR','outstandingAP'));
    }

    public function edit(Contact $contact)
    {
        $companyId  = auth()->user()->company_id;
        $groups     = ContactGroup::where('company_id',$companyId)->get();
        $arAccounts = Account::where('company_id',$companyId)->where('sub_type','receivable')->where('is_active',true)->get();
        $apAccounts = Account::where('company_id',$companyId)->where('sub_type','payable')->where('is_active',true)->get();

        return view('master.contacts.edit', compact('contact','groups','arAccounts','apAccounts'));
    }

    public function update(Request $request, Contact $contact)
    {
        $companyId = auth()->user()->company_id;
        $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:customer,vendor,both,employee',
            'email' => 'nullable|email',
            'code'  => "required|unique:contacts,code,{$contact->id},id,company_id,{$companyId}",
        ]);

        $contact->update($request->except(['_token','_method']));

        return redirect()->route('contacts.show',$contact)->with('success','Kontak berhasil diperbarui!');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success','Kontak berhasil dihapus!');
    }

    private function generateCode(int $companyId, string $type): string
    {
        $prefix = $type === 'customer' ? 'CUS' : 'VND';
        $last   = Contact::where('company_id',$companyId)->where('type',$type)->orderBy('id','desc')->first();
        $seq    = $last ? (intval(substr($last->code,-3))+1) : 1;
        return sprintf('%s-%03d', $prefix, $seq);
    }
}