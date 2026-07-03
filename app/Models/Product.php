<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'product_category_id', 'unit_id', 'code', 'barcode', 'name',
        'description', 'type', 'selling_price', 'purchase_price', 'hpp',
        'min_stock', 'max_stock', 'opening_stock', 'image', 'is_sold', 'is_purchased',
        'track_inventory', 'sales_account_id', 'purchase_account_id', 'inventory_account_id',
        'cogs_account_id', 'costing_method', 'is_active'
    ];

    protected $casts = [
        'selling_price' => 'decimal:4',
        'purchase_price' => 'decimal:4',
        'hpp' => 'decimal:4',
        'min_stock' => 'decimal:4',
        'max_stock' => 'decimal:4',
        'opening_stock' => 'decimal:4',
        'is_sold' => 'boolean',
        'is_purchased' => 'boolean',
        'track_inventory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_account_id');
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stocks->sum('quantity');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'inventory' => 'Persediaan',
            'service' => 'Jasa',
            'non_inventory' => 'Non-Persediaan',
            default => 'Unknown',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'inventory' => 'primary',
            'service' => 'success',
            'non_inventory' => 'warning',
            default => 'secondary',
        };
    }
}