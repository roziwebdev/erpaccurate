<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoiceItem extends Model
{
    protected $fillable = ['purchase_invoice_id','product_id','purchase_order_item_id','unit_id','warehouse_id','account_id','description','quantity','unit_price','discount_percent','discount_amount','tax_percent','tax_amount','total','sort_order'];
    protected $casts    = ['quantity'=>'decimal:4','unit_price'=>'decimal:4','total'=>'decimal:4'];

    public function purchaseInvoice(): BelongsTo  { return $this->belongsTo(PurchaseInvoice::class); }
    public function product(): BelongsTo          { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo             { return $this->belongsTo(Unit::class); }
}