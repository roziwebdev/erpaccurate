<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'account_category_id', 'parent_id', 'code', 'name',
        'description', 'type', 'sub_type', 'opening_balance', 'current_balance',
        'is_header', 'is_active', 'is_system', 'level'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:4',
        'current_balance' => 'decimal:4',
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function getBalanceAttribute(): float
    {
        return $this->current_balance;
    }

    public function isDebitNormal(): bool
    {
        return in_array($this->type, ['asset', 'expense']);
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'asset' => 'primary',
            'liability' => 'danger',
            'equity' => 'success',
            'revenue' => 'info',
            'expense' => 'warning',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'asset' => 'Aset',
            'liability' => 'Kewajiban',
            'equity' => 'Ekuitas',
            'revenue' => 'Pendapatan',
            'expense' => 'Beban',
            default => 'Unknown',
        };
    }
}