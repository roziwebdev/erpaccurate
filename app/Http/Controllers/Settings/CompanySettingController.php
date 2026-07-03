<?php
// app/Http/Controllers/Settings/CompanySettingController.php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email',
            'logo'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $company = auth()->user()->company;
        $data    = $request->except(['_token','_method','logo']);

        if ($request->hasFile('logo')) {
            if ($company->logo) Storage::disk('public')->delete($company->logo);
            $data['logo'] = $request->file('logo')->store('logos','public');
        }

        $company->update($data);

        return redirect()->route('settings.company')->with('success','Pengaturan perusahaan berhasil disimpan!');
    }
}
