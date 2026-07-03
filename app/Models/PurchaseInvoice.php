<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use SoftDeletes;
    protected $fillable = ['company_id','contact_id','warehouse_id','created_by','purchase_order_id','number','vendor_invoice_number','date','due_date','status','reference','notes','subtotal','discount_amount','tax_amount','shipping_cost','total','paid_amount','remaining_amount','currency_code','exchange_rate','ap_account_id'];
    protected $casts    = ['date'=>'date','due_date'=>'date','total'=>'decimal:4','paid_amount'=>'decimal:4','remaining_amount'=>'decimal:4'];

    public function company(): BelongsTo        { return $this->belongsTo(Company::class); }
    public function contact(): BelongsTo        { return $this->belongsTo(Contact::class); }
    public function warehouse(): BelongsTo      { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class,'created_by'); }
    public function purchaseOrder(): BelongsTo  { return $this->belongsTo(PurchaseOrder::class); }
    public function items(): HasMany            { return $this->hasMany(PurchaseInvoiceItem::class); }
    public function apAccount(): BelongsTo      { return $this->belongsTo(Account::class,'ap_account_id'); }

    public function getStatusColorAttribute(): string {
        return match($this->status) { 'draft'=>'secondary','posted'=>'info','partial'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark', default=>'secondary' };
    }

    public function getStatusLabelAttribute(): string {
        return match($this->status) { 'draft'=>'Draft','posted'=>'Diposting','partial'=>'Dibayar Sebagian','paid'=>'Lunas','overdue'=>'Jatuh Tempo','cancelled'=>'Dibatalkan', default=>'Unknown' };
    }
}