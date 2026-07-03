<?php
// app/Models/SalesInvoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'contact_id', 'warehouse_id', 'created_by', 'sales_order_id',
        'number', 'date', 'due_date', 'status', 'reference', 'notes', 'shipping_address',
        'subtotal', 'discount_percent', 'discount_amount', 'tax_amount', 'shipping_cost',
        'total', 'paid_amount', 'remaining_amount', 'currency_code', 'exchange_rate', 'ar_account_id'
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:4',
        'discount_percent' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'shipping_cost' => 'decimal:4',
        'total' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'remaining_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(SalesReceiptItem::class);
    }

    public function arAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'ar_account_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'partial' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'partial' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
            'cancelled' => 'Dibatalkan',
            default => 'Unknown',
        };
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' &&
               $this->status !== 'cancelled' &&
               $this->due_date->isPast();
    }
}