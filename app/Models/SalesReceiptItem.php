<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReceiptItem extends Model
{
    protected $fillable = ['sales_receipt_id','sales_invoice_id','amount','discount'];
    protected $casts    = ['amount'=>'decimal:4','discount'=>'decimal:4'];

    public function receipt(): BelongsTo { return $this->belongsTo(SalesReceipt::class,'sales_receipt_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(SalesInvoice::class,'sales_invoice_id'); }
}