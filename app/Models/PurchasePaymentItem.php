<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePaymentItem extends Model
{
    protected $fillable = ['purchase_payment_id','purchase_invoice_id','amount','discount'];
    protected $casts    = ['amount'=>'decimal:4','discount'=>'decimal:4'];

    public function payment(): BelongsTo { return $this->belongsTo(PurchasePayment::class,'purchase_payment_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(PurchaseInvoice::class,'purchase_invoice_id'); }
}