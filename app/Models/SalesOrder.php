<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use SoftDeletes;
    protected $fillable = ['company_id','contact_id','warehouse_id','created_by','number','date','due_date','delivery_date','status','reference','notes','shipping_address','subtotal','discount_percent','discount_amount','tax_amount','shipping_cost','total','currency_code','exchange_rate'];
    protected $casts    = ['date'=>'date','due_date'=>'date','delivery_date'=>'date'];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function contact(): BelongsTo    { return $this->belongsTo(Contact::class); }
    public function warehouse(): BelongsTo  { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class,'created_by'); }
    public function items(): HasMany        { return $this->hasMany(SalesOrderItem::class); }
    public function invoices(): HasMany     { return $this->hasMany(SalesInvoice::class); }

    public function getStatusColorAttribute(): string {
        return match($this->status) { 'draft'=>'secondary','confirmed'=>'info','partial'=>'warning','delivered'=>'success','invoiced'=>'primary','cancelled'=>'dark', default=>'secondary' };
    }
}