<?php

// app/Models/StockTransfer.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'company_id','from_warehouse_id','to_warehouse_id','created_by',
        'number','date','status','reference','notes'
    ];
    protected $casts = ['date' => 'date'];

    public function company(): BelongsTo       { return $this->belongsTo(Company::class); }
    public function fromWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse(): BelongsTo   { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany           { return $this->hasMany(StockTransferItem::class); }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'draft'=>'secondary','posted'=>'success','cancelled'=>'danger', default=>'secondary'
        };
    }
}