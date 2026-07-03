<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoiceItem extends Model
{
    protected $fillable = ['sales_invoice_id','product_id','sales_order_item_id','unit_id','warehouse_id','description','quantity','unit_price','discount_percent','discount_amount','tax_percent','tax_amount','total','hpp','sort_order'];
    protected $casts    = ['quantity'=>'decimal:4','unit_price'=>'decimal:4','total'=>'decimal:4','hpp'=>'decimal:4'];

    public function salesInvoice(): BelongsTo     { return $this->belongsTo(SalesInvoice::class); }
    public function product(): BelongsTo          { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo             { return $this->belongsTo(Unit::class); }
    public function salesOrderItem(): BelongsTo   { return $this->belongsTo(SalesOrderItem::class); }
}