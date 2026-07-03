<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReceiveItemDetail extends Model
{
    protected $fillable = [
        'receive_item_id','product_id','purchase_order_item_id',
        'unit_id','description','quantity','unit_price','sort_order'
    ];
    protected $casts = ['quantity'=>'decimal:4','unit_price'=>'decimal:4'];

    public function receiveItem(): BelongsTo        { return $this->belongsTo(ReceiveItem::class,'receive_item_id'); }
    public function product(): BelongsTo            { return $this->belongsTo(Product::class); }
    public function purchaseOrderItem(): BelongsTo  { return $this->belongsTo(PurchaseOrderItem::class); }
    public function unit(): BelongsTo               { return $this->belongsTo(Unit::class); }
}