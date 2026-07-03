<?php
// app/Http/Controllers/Master/TaxController.php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $cid  = auth()->user()->company_id;
        $taxes= Tax::where('company_id',$cid)->with(['salesAccount','purchaseAccount'])->get();
        return view('master.taxes.index', compact('taxes'));
    }

    public function create()
    {
        $cid      = auth()->user()->company_id;
        $accounts = Account::where('company_id',$cid)->where('is_active',true)->where('is_header',false)->orderBy('code')->get();
        return view('master.taxes.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $cid = auth()->user()->company_id;
        $request->validate(['code'=>"required|unique:taxes,code,NULL,id,company_id,{$cid}",'name'=>'required','rate'=>'required|numeric|min:0|max:100','type'=>'required']);
        Tax::create(array_merge($request->all(),['company_id'=>$cid]));
        return redirect()->route('taxes.index')->with('success','Pajak berhasil ditambahkan!');
    }

    public function edit(Tax $tax)
    {
        $cid      = auth()->user()->company_id;
        $accounts = Account::where('company_id',$cid)->where('is_active',true)->where('is_header',false)->orderBy('code')->get();
        return view('master.taxes.edit', compact('tax','accounts'));
    }

    public function update(Request $request, Tax $tax)
    {
        $cid = auth()->user()->company_id;
        $request->validate(['code'=>"required|unique:taxes,code,{$tax->id},id,company_id,{$cid}",'name'=>'required','rate'=>'required|numeric|min:0|max:100']);
        $tax->update($request->all());
        return redirect()->route('taxes.index')->with('success','Pajak berhasil diperbarui!');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return redirect()->route('taxes.index')->with('success','Pajak berhasil dihapus!');
    }
}


