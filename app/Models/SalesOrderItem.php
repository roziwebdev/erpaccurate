<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrderItem extends Model
{
    protected $fillable = ['sales_order_id','product_id','unit_id','warehouse_id','description','quantity','unit_price','discount_percent','discount_amount','tax_percent','tax_amount','total','delivered_qty','invoiced_qty','sort_order'];
    protected $casts    = ['quantity'=>'decimal:4','unit_price'=>'decimal:4','total'=>'decimal:4'];

    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo       { return $this->belongsTo(Unit::class); }
}
