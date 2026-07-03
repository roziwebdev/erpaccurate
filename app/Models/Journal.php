<?php
// app/Models/Journal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'created_by', 'number', 'date', 'type', 'description',
        'reference', 'reference_type', 'reference_id', 'status', 'is_adjusting',
        'is_closing', 'total_debit', 'total_credit'
    ];

    protected $casts = [
        'date' => 'date',
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
        'is_adjusting' => 'boolean',
        'is_closing' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'posted' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'general' => 'Jurnal Umum',
            'sales' => 'Jurnal Penjualan',
            'purchase' => 'Jurnal Pembelian',
            'payment' => 'Jurnal Pembayaran',
            'receipt' => 'Jurnal Penerimaan',
            'adjustment' => 'Penyesuaian',
            'opening' => 'Saldo Awal',
            'closing' => 'Penutup',
            'inventory' => 'Persediaan',
            default => 'Unknown',
        };
    }
}