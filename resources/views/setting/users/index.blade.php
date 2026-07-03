{{-- resources/views/settings/users/index.blade.php --}}
@extends('layouts.app')
@section('title','Manajemen Pengguna')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a><span class="separator">/</span>
    <span class="current">Pengguna</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Manajemen Pengguna</h1><p class="page-subtitle">Kelola akun pengguna dan hak akses</p></div>
    <a href="{{ route('settings.users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pengguna</a>
</div>
<div class="card">
    <div style="overflow-x:auto;">
        <table class="erp-table">
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Role</th><th>Telepon</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;">
                                {{ strtoupper(substr($user->name,0,2)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;">{{ $user->name }}</div>
                                @if($user->id===auth()->id())<span style="font-size:11px;color:#4f46e5;">(Anda)</span>@endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-primary">{{ $user->role?->display_name ?? '-' }}</span></td>
                    <td>{{ $user->phone ?? '-' }}</td>
                    <td>
                        @if($user->is_active)<span class="badge badge-success">Aktif</span>
                        @else<span class="badge badge-secondary">Non-Aktif</span>@endif
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('settings.users.edit',$user) }}" class="btn btn-icon btn-secondary"><i class="fas fa-edit"></i></a>
                            @if($user->id!==auth()->id())
                            <form method="POST" action="{{ route('settings.users.destroy',$user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;">Belum ada pengguna</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">{{ $users->links() }}</div>
    @endif
</div>
@endsection