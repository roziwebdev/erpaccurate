<?php
// app/Models/Contact.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'contact_group_id', 'code', 'name', 'alias', 'type',
        'npwp', 'pkp_number', 'billing_address', 'billing_city', 'billing_province',
        'billing_postal_code', 'billing_country', 'shipping_address', 'shipping_city',
        'phone', 'fax', 'email', 'website', 'contact_person', 'credit_limit',
        'payment_term', 'currency_code', 'receivable_account_id', 'payable_account_id',
        'opening_balance', 'is_active', 'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:4',
        'opening_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ContactGroup::class, 'contact_group_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ContactAddress::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function isCustomer(): bool
    {
        return in_array($this->type, ['customer', 'both']);
    }

    public function isVendor(): bool
    {
        return in_array($this->type, ['vendor', 'both']);
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'customer' => 'primary',
            'vendor' => 'warning',
            'both' => 'success',
            'employee' => 'info',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'customer' => 'Pelanggan',
            'vendor' => 'Pemasok',
            'both' => 'Keduanya',
            'employee' => 'Karyawan',
            default => 'Unknown',
        };
    }
}