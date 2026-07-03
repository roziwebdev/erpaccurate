<?php

// app/Models/ReceiveItem.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiveItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'company_id','contact_id','warehouse_id','created_by',
        'purchase_order_id','number','date','status','reference','notes'
    ];
    protected $casts = ['date'=>'date'];

    public function company(): BelongsTo       { return $this->belongsTo(Company::class); }
    public function contact(): BelongsTo       { return $this->belongsTo(Contact::class); }
    public function warehouse(): BelongsTo     { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class,'created_by'); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function items(): HasMany           { return $this->hasMany(ReceiveItemDetail::class); }

    public function getStatusColorAttribute(): string {
        return match($this->status){'draft'=>'secondary','posted'=>'success','cancelled'=>'danger',default=>'secondary'};
    }
}