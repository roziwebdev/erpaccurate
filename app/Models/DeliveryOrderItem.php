<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrderItem extends Model
{
    protected $fillable = ['delivery_order_id','product_id','sales_order_item_id','unit_id','description','quantity','sort_order'];
    protected $casts    = ['quantity'=>'decimal:4'];

    public function deliveryOrder(): BelongsTo  { return $this->belongsTo(DeliveryOrder::class); }
    public function product(): BelongsTo        { return $this->belongsTo(Product::class); }
    public function salesOrderItem(): BelongsTo { return $this->belongsTo(SalesOrderItem::class); }
    public function unit(): BelongsTo           { return $this->belongsTo(Unit::class); }
}
