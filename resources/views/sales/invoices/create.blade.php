{{-- resources/views/sales/invoices/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Buat Faktur Penjualan')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator">/</span>
    <a href="{{ route('sales.invoices.index') }}">Faktur Penjualan</a>
    <span class="separator">/</span>
    <span class="current">Buat Faktur</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Buat Faktur Penjualan</h1>
        <p class="page-subtitle">Isi detail faktur penjualan baru</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<form method="POST" action="{{ route('sales.invoices.store') }}" id="invoiceForm">
    @csrf

    <div class="grid gap-6" style="grid-template-columns:2fr 1fr;">
        <!-- Left Column -->
        <div>
            <!-- Header Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-file-invoice" style="color:#4f46e5;margin-right:8px;"></i>Informasi Faktur</span>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label required">Nomor Faktur</label>
                            <input type="text" name="number" value="{{ $nextNumber }}" class="form-control @error('number') is-invalid @enderror" required>
                            @error('number')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control datepicker @error('date') is-invalid @enderror" required>
                            @error('date')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Pelanggan</label>
                            <select name="contact_id" class="form-control select2 @error('contact_id') is-invalid @enderror" required id="customerSelect" onchange="loadCustomerData(this)">
                                <option value="">Pilih Pelanggan...</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-payment-term="{{ $c->payment_term }}" {{ old('contact_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('contact_id')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" class="form-control datepicker @error('due_date') is-invalid @enderror" required id="dueDateInput">
                            @error('due_date')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gudang</label>
                            <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror">
                                <option value="">Pilih Gudang...</option>
                                @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('warehouse_id')==$w->id?'selected':'' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Referensi</label>
                            <input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="No. PO Pelanggan">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Akun Piutang (AR)</label>
                            <select name="ar_account_id" class="form-control select2 @error('ar_account_id') is-invalid @enderror" required>
                                <option value="">Pilih Akun AR...</option>
                                @foreach($arAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('ar_account_id')==$acc->id?'selected':'' }}>{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                            @error('ar_account_id')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Pengiriman</label>
                            <textarea name="shipping_address" class="form-control" rows="2" placeholder="Alamat pengiriman (opsional)">{{ old('shipping_address') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-list" style="color:#4f46e5;margin-right:8px;"></i>Detail Item</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="invoice-items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:30px;">#</th>
                                <th style="min-width:200px;">Produk/Item</th>
                                <th style="min-width:130px;">Deskripsi</th>
                                <th style="width:80px;">Satuan</th>
                                <th style="width:90px;">Qty</th>
                                <th style="width:130px;">Harga Satuan</th>
                                <th style="width:80px;">Disc%</th>
                                <th style="width:80px;">Pajak%</th>
                                <th style="width:120px;">Subtotal</th>
                                <th style="width:36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 16px;background:#f8fafc;border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus"></i> + Tambah Item
                    </button>
                </div>
            </div>

            <!-- Notes -->
            <div class="card">
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan atau syarat pembayaran...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Summary -->
        <div>
            <div class="card" style="position:sticky;top:88px;">
                <div class="card-header">
                    <span class="card-title">Ringkasan</span>
                </div>
                <div class="card-body">
                    <!-- Discount -->
                    <div class="form-group">
                        <label class="form-label">Diskon Tambahan (%)</label>
                        <input type="number" name="discount_percent" id="discountPercent" value="{{ old('discount_percent', 0) }}"
                               class="form-control" min="0" max="100" step="0.01" oninput="calculateTotals()">
                    </div>

                    <!-- Shipping -->
                    <div class="form-group">
                        <label class="form-label">Ongkos Kirim</label>
                        <input type="number" name="shipping_cost" id="shippingCost" value="{{ old('shipping_cost', 0) }}"
                               class="form-control" min="0" step="1000" oninput="calculateTotals()">
                    </div>

                    <div style="border-top:1px solid #f1f5f9;margin:16px 0;"></div>

                    <!-- Totals -->
                    <div style="font-size:13.5px;">
                        <div class="flex justify-between" style="margin-bottom:10px;">
                            <span style="color:#64748b;">Subtotal</span>
                            <span id="subtotalDisplay" style="font-weight:500;">Rp 0</span>
                        </div>
                        <div class="flex justify-between" style="margin-bottom:10px;">
                            <span style="color:#64748b;">Total Diskon</span>
                            <span id="discountDisplay" style="font-weight:500;color:#ef4444;">- Rp 0</span>
                        </div>
                        <div class="flex justify-between" style="margin-bottom:10px;">
                            <span style="color:#64748b;">Total Pajak</span>
                            <span id="taxDisplay" style="font-weight:500;">Rp 0</span>
                        </div>
                        <div class="flex justify-between" style="margin-bottom:10px;">
                            <span style="color:#64748b;">Ongkos Kirim</span>
                            <span id="shippingDisplay" style="font-weight:500;">Rp 0</span>
                        </div>
                        <div style="border-top:2px solid #e2e8f0;margin:12px 0;padding-top:12px;" class="flex justify-between">
                            <span style="font-weight:700;font-size:15px;">TOTAL</span>
                            <span id="totalDisplay" style="font-weight:700;font-size:18px;color:#4f46e5;">Rp 0</span>
                        </div>
                    </div>

                    <div style="border-top:1px solid #f1f5f9;margin:16px 0;"></div>

                    <!-- Actions -->
                    <div class="flex" style="flex-direction:column;gap:10px;">
                        <button type="submit" name="action" value="save" class="btn btn-primary" style="width:100%;justify-content:center;">
                            <i class="fas fa-save"></i> Simpan sebagai Draft
                        </button>
                        <button type="submit" name="action" value="post" class="btn btn-success" style="width:100%;justify-content:center;">
                            <i class="fas fa-paper-plane"></i> Simpan & Posting
                        </button>
                        <a href="{{ route('sales.invoices.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Product Data -->
<script>
const products = @json($products->map(fn($p) => [
    'id' => $p->id,
    'code' => $p->code,
    'name' => $p->name,
    'selling_price' => $p->selling_price,
    'unit_id' => $p->unit_id,
    'unit_name' => $p->unit?->symbol ?? '',
    'stock' => $p->total_stock,
    'type' => $p->type,
]));

const taxes = @json($taxes->map(fn($t) => [
    'id' => $t->id,
    'code' => $t->code,
    'name' => $t->name,
    'rate' => $t->rate,
]));

let itemCount = 0;

function addItem(data = null) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.setAttribute('data-index', itemCount);
    tr.innerHTML = `
        <td style="text-align:center;color:#94a3b8;font-size:12px;">${itemCount + 1}</td>
        <td>
            <select name="items[${itemCount}][product_id]" class="item-product" onchange="onProductChange(this, ${itemCount})" required style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:13px;font-family:'Inter',sans-serif;outline:none;background:white;">
                <option value="">Pilih Produk...</option>
                ${products.map(p => `<option value="${p.id}" data-price="${p.selling_price}" data-unit="${p.unit_id}" data-unit-name="${p.unit_name}" data-stock="${p.stock}">${p.code} - ${p.name}</option>`).join('')}
            </select>
        </td>
        <td><input type="text" name="items[${itemCount}][description]" class="item-desc" placeholder="Deskripsi..."></td>
        <td>
            <input type="text" name="items[${itemCount}][unit_id]" class="item-unit" id="unit_${itemCount}" readonly style="background:#f8fafc;text-align:center;">
        </td>
        <td>
            <input type="number" name="items[${itemCount}][quantity]" class="item-qty" value="1" min="0.0001" step="0.01" oninput="calculateRow(${itemCount}); calculateTotals();" required>
        </td>
        <td>
            <input type="number" name="items[${itemCount}][unit_price]" class="item-price" value="0" min="0" step="1" oninput="calculateRow(${itemCount}); calculateTotals();">
        </td>
        <td>
            <input type="number" name="items[${itemCount}][discount_percent]" class="item-disc" value="0" min="0" max="100" step="0.01" oninput="calculateRow(${itemCount}); calculateTotals();">
        </td>
        <td>
            <input type="number" name="items[${itemCount}][tax_percent]" class="item-tax" value="11" min="0" max="100" step="0.01" oninput="calculateRow(${itemCount}); calculateTotals();">
        </td>
        <td>
            <input type="number" name="items[${itemCount}][total]" class="item-total" readonly style="background:#f8fafc;font-weight:600;text-align:right;">
        </td>
        <td>
            <button type="button" onclick="removeItem(this)" style="background:#fee2e2;color:#ef4444;border:none;border-radius:6px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    itemCount++;
    calculateTotals();
}

function onProductChange(select, index) {
    const option = select.options[select.selectedIndex];
    const price = option.getAttribute('data-price') || 0;
    const unitName = option.getAttribute('data-unit-name') || '';
    const unitId = option.getAttribute('data-unit') || '';

    const row = select.closest('tr');
    row.querySelector('.item-price').value = price;
    row.querySelector('.item-unit').value = unitName;

    calculateRow(index);
    calculateTotals();
}

function calculateRow(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    if (!row) return;

    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const disc = parseFloat(row.querySelector('.item-disc').value) || 0;
    const tax = parseFloat(row.querySelector('.item-tax').value) || 0;

    const gross = qty * price;
    const discAmount = gross * (disc / 100);
    const subtotal = gross - discAmount;
    const taxAmount = subtotal * (tax / 100);
    const total = subtotal + taxAmount;

    row.querySelector('.item-total').value = total.toFixed(0);
}

function removeItem(btn) {
    btn.closest('tr').remove();
    renumberRows();
    calculateTotals();
}

function renumberRows() {
    document.querySelectorAll('#itemsBody tr').forEach((tr, i) => {
        tr.cells[0].textContent = i + 1;
    });
}

function calculateTotals() {
    let subtotal = 0, discountTotal = 0, taxTotal = 0;

    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const disc = parseFloat(row.querySelector('.item-disc')?.value) || 0;
        const tax = parseFloat(row.querySelector('.item-tax')?.value) || 0;

        const gross = qty * price;
        const discAmount = gross * (disc / 100);
        const itemSubtotal = gross - discAmount;
        const taxAmount = itemSubtotal * (tax / 100);

        subtotal += gross;
        discountTotal += discAmount;
        taxTotal += taxAmount;
    });

    const discPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
    const shipping = parseFloat(document.getElementById('shippingCost').value) || 0;
    const additionalDiscount = subtotal * (discPercent / 100);
    discountTotal += additionalDiscount;

    const total = subtotal - discountTotal + taxTotal + shipping;

    document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('discountDisplay').textContent = '- Rp ' + discountTotal.toLocaleString('id-ID');
    document.getElementById('taxDisplay').textContent = 'Rp ' + taxTotal.toLocaleString('id-ID');
    document.getElementById('shippingDisplay').textContent = 'Rp ' + shipping.toLocaleString('id-ID');
    document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function loadCustomerData(select) {
    const option = select.options[select.selectedIndex];
    const paymentTerm = parseInt(option.getAttribute('data-payment-term') || '30');
    const date = document.querySelector('[name="date"]').value;

    if (date) {
        const dueDate = new Date(date);
        dueDate.setDate(dueDate.getDate() + paymentTerm);
        document.getElementById('dueDateInput').value = dueDate.toISOString().split('T')[0];
    }
}

// Add first item on load
document.addEventListener('DOMContentLoaded', () => {
    addItem();
});
</script>
@endsection