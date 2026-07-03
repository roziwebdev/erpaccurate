{{-- resources/views/finance/journals/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Buat Jurnal Umum')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <a href="{{ route('finance.journals.index') }}">Jurnal Umum</a>
    <span class="separator">/</span>
    <span class="current">Buat Jurnal</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Buat Jurnal Umum</h1>
        <p class="page-subtitle">Input transaksi jurnal umum (double-entry bookkeeping)</p>
    </div>
    <a href="{{ route('finance.journals.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<form method="POST" action="{{ route('finance.journals.store') }}" id="journalForm">
@csrf
<div class="grid gap-6" style="grid-template-columns:2fr 1fr;">
    <!-- Left -->
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-book" style="color:#4f46e5;margin-right:8px;"></i>Informasi Jurnal</span>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label required">Nomor Jurnal</label>
                        <input type="text" name="number" value="{{ old('number',$nextNumber) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Tanggal</label>
                        <input type="date" name="date" value="{{ old('date',date('Y-m-d')) }}" class="form-control datepicker" required>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label required">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" required placeholder="Keterangan transaksi...">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Referensi</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="No. referensi...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entries -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-list" style="color:#4f46e5;margin-right:8px;"></i>Entri Jurnal</span>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addEntry()">
                    <i class="fas fa-plus"></i> Tambah Baris
                </button>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:11px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;min-width:220px;">Akun</th>
                            <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:11px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;min-width:160px;">Kontak</th>
                            <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:11px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;min-width:150px;">Keterangan</th>
                            <th style="padding:10px 12px;text-align:right;font-weight:600;font-size:11px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;width:140px;">Debit (Rp)</th>
                            <th style="padding:10px 12px;text-align:right;font-weight:600;font-size:11px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;width:140px;">Kredit (Rp)</th>
                            <th style="padding:10px 12px;border-bottom:1px solid #e2e8f0;width:36px;"></th>
                        </tr>
                    </thead>
                    <tbody id="entriesBody"></tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;">
                            <td colspan="3" style="padding:10px 12px;font-weight:700;font-size:13px;">TOTAL</td>
                            <td style="padding:10px 12px;text-align:right;font-weight:700;font-size:14px;color:#4f46e5;" id="totalDebitDisplay">Rp 0</td>
                            <td style="padding:10px 12px;text-align:right;font-weight:700;font-size:14px;color:#4f46e5;" id="totalCreditDisplay">Rp 0</td>
                            <td></td>
                        </tr>
                        <tr id="balanceRow">
                            <td colspan="6" style="padding:8px 12px;text-align:center;" id="balanceIndicator">
                                <span style="font-size:13px;color:#94a3b8;">Tambahkan minimal 2 entri jurnal</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="padding:12px 16px;background:#f8fafc;border-top:1px solid #f1f5f9;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addEntry()">
                    <i class="fas fa-plus"></i> + Tambah Baris
                </button>
            </div>
        </div>
    </div>

    <!-- Right -->
    <div>
        <div class="card" style="position:sticky;top:88px;">
            <div class="card-header"><span class="card-title">Ringkasan</span></div>
            <div class="card-body">
                <div style="font-size:13.5px;">
                    <div class="flex justify-between" style="margin-bottom:12px;">
                        <span style="color:#64748b;">Total Debit</span>
                        <span id="summDebit" style="font-weight:700;font-size:16px;color:#4f46e5;">Rp 0</span>
                    </div>
                    <div class="flex justify-between" style="margin-bottom:12px;">
                        <span style="color:#64748b;">Total Kredit</span>
                        <span id="summCredit" style="font-weight:700;font-size:16px;color:#4f46e5;">Rp 0</span>
                    </div>
                    <div class="flex justify-between" style="margin-bottom:12px;padding-top:12px;border-top:1px solid #f1f5f9;">
                        <span style="color:#64748b;">Selisih</span>
                        <span id="summDiff" style="font-weight:700;font-size:16px;">Rp 0</span>
                    </div>
                    <div id="balanceStatus" style="text-align:center;padding:10px;border-radius:10px;margin-bottom:16px;background:#f8fafc;font-size:13px;font-weight:600;">
                        Belum ada entri
                    </div>
                </div>

                <div class="flex" style="flex-direction:column;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan Draft
                    </button>
                    <a href="{{ route('finance.journals.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>

                <div style="margin-top:16px;padding:12px;background:#ede9fe;border-radius:10px;font-size:12px;color:#5b21b6;">
                    <strong><i class="fas fa-info-circle"></i> Tips:</strong><br>
                    Jurnal harus balance (Total Debit = Total Kredit) sebelum dapat diposting.
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<script>
const accounts = @json($accounts->map(fn($a)=>['id'=>$a->id,'code'=>$a->code,'name'=>$a->name,'type'=>$a->type]));
const contacts  = @json($contacts->map(fn($c)=>['id'=>$c->id,'name'=>$c->name]));
let entryCount  = 0;

function addEntry(data = null) {
    const tbody = document.getElementById('entriesBody');
    const tr    = document.createElement('tr');
    tr.setAttribute('data-entry', entryCount);
    tr.innerHTML = `
        <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;">
            <select name="entries[${entryCount}][account_id]" class="entry-account" onchange="updateTotals()" required
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:12.5px;font-family:'Inter',sans-serif;outline:none;background:white;">
                <option value="">Pilih Akun...</option>
                ${accounts.map(a=>`<option value="${a.id}">[${a.code}] ${a.name}</option>`).join('')}
            </select>
        </td>
        <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;">
            <select name="entries[${entryCount}][contact_id]"
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:12.5px;font-family:'Inter',sans-serif;outline:none;background:white;">
                <option value="">- Tidak ada -</option>
                ${contacts.map(c=>`<option value="${c.id}">${c.name}</option>`).join('')}
            </select>
        </td>
        <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;">
            <input type="text" name="entries[${entryCount}][description]" placeholder="Keterangan..."
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:12.5px;font-family:'Inter',sans-serif;outline:none;">
        </td>
        <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;">
            <input type="number" name="entries[${entryCount}][debit]" value="0" min="0" step="1" class="entry-debit"
                oninput="updateTotals()"
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:12.5px;font-family:'Inter',sans-serif;outline:none;text-align:right;">
        </td>
        <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;">
            <input type="number" name="entries[${entryCount}][credit]" value="0" min="0" step="1" class="entry-credit"
                oninput="updateTotals()"
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:12.5px;font-family:'Inter',sans-serif;outline:none;text-align:right;">
        </td>
        <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;text-align:center;">
            <button type="button" onclick="removeEntry(this)"
                style="background:#fee2e2;color:#ef4444;border:none;border-radius:6px;width:28px;height:28px;cursor:pointer;font-size:12px;">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    entryCount++;
    updateTotals();
}

function removeEntry(btn) {
    btn.closest('tr').remove();
    updateTotals();
}

function updateTotals() {
    let totalDebit = 0, totalCredit = 0;
    document.querySelectorAll('.entry-debit').forEach(i => totalDebit  += parseFloat(i.value)||0);
    document.querySelectorAll('.entry-credit').forEach(i => totalCredit += parseFloat(i.value)||0);

    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
    const diff = Math.abs(totalDebit - totalCredit);
    const isBalanced = diff < 0.01 && totalDebit > 0;

    document.getElementById('totalDebitDisplay').textContent  = fmt(totalDebit);
    document.getElementById('totalCreditDisplay').textContent = fmt(totalCredit);
    document.getElementById('summDebit').textContent          = fmt(totalDebit);
    document.getElementById('summCredit').textContent         = fmt(totalCredit);
    document.getElementById('summDiff').textContent           = fmt(diff);
    document.getElementById('summDiff').style.color           = isBalanced ? '#10b981' : '#ef4444';

    const status = document.getElementById('balanceStatus');
    const indicator = document.getElementById('balanceIndicator');

    if (totalDebit === 0 && totalCredit === 0) {
        status.textContent = 'Belum ada entri';
        status.style.background = '#f8fafc';
        status.style.color = '#94a3b8';
    } else if (isBalanced) {
        status.innerHTML = '<i class="fas fa-check-circle"></i> BALANCE - Jurnal siap disimpan';
        status.style.background = '#d1fae5';
        status.style.color = '#065f46';
        indicator.innerHTML = '<span style="color:#10b981;font-size:13px;font-weight:600;"><i class="fas fa-check-circle"></i> Jurnal Balance!</span>';
    } else {
        status.innerHTML = `<i class="fas fa-exclamation-triangle"></i> TIDAK BALANCE (Selisih: ${fmt(diff)})`;
        status.style.background = '#fee2e2';
        status.style.color = '#991b1b';
        indicator.innerHTML = `<span style="color:#ef4444;font-size:13px;font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Tidak Balance! Selisih: ${fmt(diff)}</span>`;
    }
}

// Add 2 default rows
document.addEventListener('DOMContentLoaded', () => { addEntry(); addEntry(); });
</script>
@endsection