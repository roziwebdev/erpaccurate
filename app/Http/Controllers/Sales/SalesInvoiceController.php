<?php
// app/Http/Controllers/Sales/SalesInvoiceController.php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = SalesInvoice::where('company_id', $companyId)
            ->with(['contact', 'createdBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('number', 'like', "%{$request->search}%")
                  ->orWhereHas('contact', fn($cq) => $cq->where('name', 'like', "%{$request->search}%"));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        $totalDraft = SalesInvoice::where('company_id', $companyId)->where('status', 'draft')->sum('total');
        $totalUnpaid = SalesInvoice::where('company_id', $companyId)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('remaining_amount');
        $totalPaid = SalesInvoice::where('company_id', $companyId)->where('status', 'paid')->whereMonth('date', now()->month)->sum('total');
        $totalOverdue = SalesInvoice::where('company_id', $companyId)->where('status', 'overdue')->sum('remaining_amount');

        $customers = Contact::where('company_id', $companyId)
            ->whereIn('type', ['customer', 'both'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sales.invoices.index', compact(
            'invoices', 'customers',
            'totalDraft', 'totalUnpaid', 'totalPaid', 'totalOverdue'
        ));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $customers = Contact::where('company_id', $companyId)
            ->whereIn('type', ['customer', 'both'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('is_sold', true)
            ->where('is_active', true)
            ->with(['unit', 'stocks'])
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $arAccounts = Account::where('company_id', $companyId)
            ->where('sub_type', 'receivable')
            ->where('is_active', true)
            ->get();

        $nextNumber = $this->generateInvoiceNumber($companyId);

        return view('sales.invoices.create', compact(
            'customers', 'products', 'warehouses', 'taxes', 'arAccounts', 'nextNumber'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'ar_account_id' => 'required|exists:accounts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $companyId = auth()->user()->company_id;

            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $qty = floatval($item['quantity']);
                $price = floatval($item['unit_price']);
                $disc = floatval($item['discount_percent'] ?? 0);
                $tax = floatval($item['tax_percent'] ?? 0);

                $itemDiscount = ($qty * $price) * ($disc / 100);
                $itemSubtotal = ($qty * $price) - $itemDiscount + floatval($item['discount_amount'] ?? 0);
                $itemTax = $itemSubtotal * ($tax / 100);

                $subtotal += $qty * $price;
                $discountAmount += $itemDiscount;
                $taxAmount += $itemTax;
            }

            $shippingCost = floatval($request->shipping_cost ?? 0);
            $discPercent = floatval($request->discount_percent ?? 0);
            $additionalDiscount = $subtotal * ($discPercent / 100);
            $discountAmount += $additionalDiscount;

            $total = $subtotal - $discountAmount + $taxAmount + $shippingCost;

            $invoice = SalesInvoice::create([
                'company_id' => $companyId,
                'contact_id' => $request->contact_id,
                'warehouse_id' => $request->warehouse_id,
                'created_by' => auth()->id(),
                'sales_order_id' => $request->sales_order_id,
                'number' => $request->number ?? $this->generateInvoiceNumber($companyId),
                'date' => $request->date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'reference' => $request->reference,
                'notes' => $request->notes,
                'shipping_address' => $request->shipping_address,
                'subtotal' => $subtotal,
                'discount_percent' => $discPercent,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'currency_code' => $request->currency_code ?? 'IDR',
                'exchange_rate' => $request->exchange_rate ?? 1,
                'ar_account_id' => $request->ar_account_id,
            ]);

            foreach ($request->items as $sortOrder => $itemData) {
                $qty = floatval($itemData['quantity']);
                $price = floatval($itemData['unit_price']);
                $disc = floatval($itemData['discount_percent'] ?? 0);
                $tax = floatval($itemData['tax_percent'] ?? 0);

                $itemDiscAmount = ($qty * $price) * ($disc / 100) + floatval($itemData['discount_amount'] ?? 0);
                $itemSubtotal = ($qty * $price) - $itemDiscAmount;
                $itemTaxAmount = $itemSubtotal * ($tax / 100);
                $itemTotal = $itemSubtotal + $itemTaxAmount;

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $itemData['product_id'],
                    'unit_id' => $itemData['unit_id'] ?? null,
                    'warehouse_id' => $itemData['warehouse_id'] ?? $request->warehouse_id,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_percent' => $disc,
                    'discount_amount' => $itemDiscAmount,
                    'tax_percent' => $tax,
                    'tax_amount' => $itemTaxAmount,
                    'total' => $itemTotal,
                    'sort_order' => $sortOrder,
                ]);
            }
        });

        return redirect()->route('sales.invoices.index')
            ->with('success', 'Faktur penjualan berhasil dibuat!');
    }

    public function show(SalesInvoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['contact', 'items.product.unit', 'items.unit', 'createdBy', 'warehouse', 'arAccount']);

        return view('sales.invoices.show', compact('invoice'));
    }

    public function edit(SalesInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya faktur dengan status draft yang dapat diedit!');
        }

        $companyId = auth()->user()->company_id;

        $invoice->load(['items.product', 'items.unit']);

        $customers = Contact::where('company_id', $companyId)
            ->whereIn('type', ['customer', 'both'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('is_sold', true)
            ->where('is_active', true)
            ->with(['unit', 'stocks'])
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $arAccounts = Account::where('company_id', $companyId)
            ->where('sub_type', 'receivable')
            ->where('is_active', true)
            ->get();

        return view('sales.invoices.edit', compact(
            'invoice', 'customers', 'products', 'warehouses', 'taxes', 'arAccounts'
        ));
    }

    public function update(Request $request, SalesInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya faktur dengan status draft yang dapat diedit!');
        }

        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $qty = floatval($item['quantity']);
                $price = floatval($item['unit_price']);
                $disc = floatval($item['discount_percent'] ?? 0);
                $tax = floatval($item['tax_percent'] ?? 0);

                $itemDiscount = ($qty * $price) * ($disc / 100);
                $itemSubtotal = ($qty * $price) - $itemDiscount;
                $itemTax = $itemSubtotal * ($tax / 100);

                $subtotal += $qty * $price;
                $discountAmount += $itemDiscount;
                $taxAmount += $itemTax;
            }

            $shippingCost = floatval($request->shipping_cost ?? 0);
            $discPercent = floatval($request->discount_percent ?? 0);
            $additionalDiscount = $subtotal * ($discPercent / 100);
            $discountAmount += $additionalDiscount;

            $total = $subtotal - $discountAmount + $taxAmount + $shippingCost;

            $invoice->update([
                'contact_id' => $request->contact_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'due_date' => $request->due_date,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'discount_percent' => $discPercent,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'remaining_amount' => $total - $invoice->paid_amount,
                'ar_account_id' => $request->ar_account_id,
            ]);

            $invoice->items()->delete();

            foreach ($request->items as $sortOrder => $itemData) {
                $qty = floatval($itemData['quantity']);
                $price = floatval($itemData['unit_price']);
                $disc = floatval($itemData['discount_percent'] ?? 0);
                $tax = floatval($itemData['tax_percent'] ?? 0);

                $itemDiscAmount = ($qty * $price) * ($disc / 100);
                $itemSubtotal = ($qty * $price) - $itemDiscAmount;
                $itemTaxAmount = $itemSubtotal * ($tax / 100);
                $itemTotal = $itemSubtotal + $itemTaxAmount;

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $itemData['product_id'],
                    'unit_id' => $itemData['unit_id'] ?? null,
                    'warehouse_id' => $itemData['warehouse_id'] ?? $request->warehouse_id,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_percent' => $disc,
                    'discount_amount' => $itemDiscAmount,
                    'tax_percent' => $tax,
                    'tax_amount' => $itemTaxAmount,
                    'total' => $itemTotal,
                    'sort_order' => $sortOrder,
                ]);
            }
        });

        return redirect()->route('sales.invoices.show', $invoice)
            ->with('success', 'Faktur penjualan berhasil diperbarui!');
    }

    public function post(SalesInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return redirect()->back()->with('error', 'Faktur sudah diposting atau dibatalkan!');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->load(['items.product', 'contact', 'arAccount']);

            // Create Journal
            $journal = Journal::create([
                'company_id' => $invoice->company_id,
                'created_by' => auth()->id(),
                'number' => 'JRN-' . date('YmdHis'),
                'date' => $invoice->date,
                'type' => 'sales',
                'description' => "Faktur Penjualan #{$invoice->number} - {$invoice->contact->name}",
                'reference_type' => SalesInvoice::class,
                'reference_id' => $invoice->id,
                'status' => 'posted',
                'total_debit' => $invoice->total,
                'total_credit' => $invoice->total,
            ]);

            // Debit AR
            JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $invoice->ar_account_id,
                'contact_id' => $invoice->contact_id,
                'description' => "Piutang - {$invoice->number}",
                'debit' => $invoice->total,
                'credit' => 0,
            ]);

            // Credit Sales & Tax
            foreach ($invoice->items as $item) {
                if ($item->product->salesAccount) {
                    JournalEntry::create([
                        'journal_id' => $journal->id,
                        'account_id' => $item->product->sales_account_id,
                        'description' => "Penjualan - {$item->product->name}",
                        'debit' => 0,
                        'credit' => $item->total - $item->tax_amount,
                    ]);
                }

                if ($item->tax_amount > 0) {
                    // Credit PPN Out
                    $ppnAccount = Account::where('company_id', $invoice->company_id)
                        ->where('sub_type', 'other_liability')
                        ->where('name', 'like', '%PPN%')
                        ->first();

                    if ($ppnAccount) {
                        JournalEntry::create([
                            'journal_id' => $journal->id,
                            'account_id' => $ppnAccount->id,
                            'description' => "PPN Keluaran - {$invoice->number}",
                            'debit' => 0,
                            'credit' => $item->tax_amount,
                        ]);
                    }
                }

                // Update Stock (Debit COGS, Credit Inventory)
                if ($item->product->type === 'inventory' && $item->product->track_inventory) {
                    $stock = ProductStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $item->warehouse_id ?? $invoice->warehouse_id)
                        ->first();

                    if ($stock) {
                        $hpp = $stock->avg_cost * $item->quantity;

                        $stock->decrement('quantity', $item->quantity);

                        // Update HPP on item
                        $item->update(['hpp' => $hpp]);

                        if ($item->product->cogsAccount && $item->product->inventoryAccount) {
                            JournalEntry::create([
                                'journal_id' => $journal->id,
                                'account_id' => $item->product->cogs_account_id,
                                'description' => "HPP - {$item->product->name}",
                                'debit' => $hpp,
                                'credit' => 0,
                            ]);

                            JournalEntry::create([
                                'journal_id' => $journal->id,
                                'account_id' => $item->product->inventory_account_id,
                                'description' => "Keluar Stock - {$item->product->name}",
                                'debit' => 0,
                                'credit' => $hpp,
                            ]);
                        }
                    }
                }
            }

            $invoice->update(['status' => 'sent']);
        });

        return redirect()->back()->with('success', 'Faktur berhasil diposting dan jurnal telah dibuat!');
    }

    public function cancel(SalesInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->back()->with('error', 'Faktur tidak dapat dibatalkan!');
        }

        $invoice->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Faktur berhasil dibatalkan!');
    }

    public function print(SalesInvoice $invoice)
    {
        $invoice->load(['contact', 'items.product.unit', 'items.unit', 'createdBy', 'warehouse', 'company']);

        return view('sales.invoices.print', compact('invoice'));
    }

    public function destroy(SalesInvoice $invoice)
    {
        $this->authorize('delete', $invoice);

        if ($invoice->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya faktur draft yang dapat dihapus!');
        }

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('sales.invoices.index')
            ->with('success', 'Faktur berhasil dihapus!');
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = SalesInvoice::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (intval(substr($lastInvoice->number, -4)) + 1) : 1;

        return sprintf('%s/%s/%s/%04d', $prefix, $year, $month, $sequence);
    }
}