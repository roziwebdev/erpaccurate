{{-- resources/views/settings/company.blade.php --}}
@extends('layouts.app')

@section('title', 'Pengaturan Perusahaan')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Pengaturan Perusahaan</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Pengaturan Perusahaan</h1>
        <p class="page-subtitle">Kelola informasi dan konfigurasi perusahaan</p>
    </div>
</div>

<div class="grid gap-6" style="grid-template-columns:240px 1fr;">
    <!-- Sidebar Menu -->
    <div>
        <div class="card" style="padding:8px;">
            @foreach([
                ['icon'=>'fas fa-building','label'=>'Informasi Perusahaan','tab'=>'company'],
                ['icon'=>'fas fa-sliders-h','label'=>'Pengaturan Umum','tab'=>'general'],
                ['icon'=>'fas fa-money-bill','label'=>'Akun Default','tab'=>'accounts'],
                ['icon'=>'fas fa-file-invoice','label'=>'Penomoran Dokumen','tab'=>'numbering'],
                ['icon'=>'fas fa-lock','label'=>'Keamanan','tab'=>'security'],
            ] as $menu)
            <a href="#{{ $menu['tab'] }}" onclick="switchTab('{{ $menu['tab'] }}')"
               class="sidebar-item {{ $loop->first ? 'active' : '' }}" id="tab-btn-{{ $menu['tab'] }}"
               style="border-radius:10px;">
                <span class="icon"><i class="{{ $menu['icon'] }}"></i></span>
                {{ $menu['label'] }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Content -->
    <div>
        <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <!-- Company Info Tab -->
            <div class="tab-content card" id="tab-company">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-building" style="color:#4f46e5;margin-right:8px;"></i>Informasi Perusahaan</span>
                </div>
                <div class="card-body">
                    @php $company = auth()->user()->company; @endphp

                    <!-- Logo Upload -->
                    <div class="form-group" style="text-align:center;margin-bottom:24px;">
                        <div style="width:100px;height:100px;border-radius:16px;background:#f1f5f9;border:2px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;overflow:hidden;cursor:pointer;" onclick="document.getElementById('logoInput').click()">
                            @if($company->logo)
                            <img src="{{ asset('storage/'.$company->logo) }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                            <div style="text-align:center;color:#94a3b8;">
                                <i class="fas fa-camera" style="font-size:24px;display:block;margin-bottom:4px;"></i>
                                <span style="font-size:11px;">Upload Logo</span>
                            </div>
                            @endif
                        </div>
                        <input type="file" id="logoInput" name="logo" accept="image/*" style="display:none;" onchange="previewLogo(this)">
                        <div style="font-size:12px;color:#94a3b8;">PNG/JPG, maks. 2MB</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label required">Nama Perusahaan</label>
                            <input type="text" name="name" value="{{ old('name',$company->name) }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Legal</label>
                            <input type="text" name="legal_name" value="{{ old('legal_name',$company->legal_name) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp',$company->npwp) }}" class="form-control" placeholder="00.000.000.0-000.000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. PKP</label>
                            <input type="text" name="pkp_number" value="{{ old('pkp_number',$company->pkp_number) }}" class="form-control">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address',$company->address) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" value="{{ old('city',$company->city) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province',$company->province) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kode Pos</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code',$company->postal_code) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Negara</label>
                            <input type="text" name="country" value="{{ old('country',$company->country) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone',$company->phone) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fax</label>
                            <input type="text" name="fax" value="{{ old('fax',$company->fax) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email',$company->email) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" value="{{ old('website',$company->website) }}" class="form-control" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mata Uang Utama</label>
                            <select name="currency_code" class="form-control">
                                <option value="IDR" {{ $company->currency_code=='IDR'?'selected':'' }}>IDR - Rupiah Indonesia</option>
                                <option value="USD" {{ $company->currency_code=='USD'?'selected':'' }}>USD - US Dollar</option>
                                <option value="SGD" {{ $company->currency_code=='SGD'?'selected':'' }}>SGD - Singapore Dollar</option>
                                <option value="EUR" {{ $company->currency_code=='EUR'?'selected':'' }}>EUR - Euro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Awal Tahun Fiskal</label>
                            <select name="fiscal_year_start" class="form-control">
                                <option value="01-01" {{ $company->fiscal_year_start=='01-01'?'selected':'' }}>1 Januari</option>
                                <option value="04-01" {{ $company->fiscal_year_start=='04-01'?'selected':'' }}>1 April</option>
                                <option value="07-01" {{ $company->fiscal_year_start=='07-01'?'selected':'' }}>1 Juli</option>
                                <option value="10-01" {{ $company->fiscal_year_start=='10-01'?'selected':'' }}>1 Oktober</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display='none');
    document.querySelectorAll('[id^="tab-btn-"]').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+tabId).style.display='block';
    document.getElementById('tab-btn-'+tabId).classList.add('active');
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const container = input.previousElementSibling;
            container.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Show first tab
document.addEventListener('DOMContentLoaded', () => switchTab('company'));
</script>
@endpush