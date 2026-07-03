<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $cid   = auth()->user()->company_id;
        $users = User::where('company_id',$cid)->with('role')->latest()->paginate(15);
        $roles = Role::all();
        return view('settings.users.index', compact('users','roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('settings.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role_id'  => 'required|exists:roles,id',
        ]);
        User::create([
            'company_id' => auth()->user()->company_id,
            'role_id'    => $request->role_id,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'phone'      => $request->phone,
            'is_active'  => $request->boolean('is_active',true),
        ]);
        return redirect()->route('settings.users.index')->with('success','Pengguna berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('settings.users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:users,email,{$user->id}",
            'role_id'  => 'required|exists:roles,id',
            'password' => 'nullable|min:8|confirmed',
        ]);
        $data = $request->only(['name','email','role_id','phone','is_active']);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);
        $user->update($data);
        return redirect()->route('settings.users.index')->with('success','Pengguna berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error','Tidak bisa menghapus akun sendiri!');
        $user->delete();
        return redirect()->route('settings.users.index')->with('success','Pengguna berhasil dihapus!');
    }
}