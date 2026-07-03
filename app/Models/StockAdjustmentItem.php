<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id','product_id','unit_id','description',
        'qty_system','qty_actual','qty_difference','unit_cost','total_cost','sort_order'
    ];
    protected $casts = [
        'qty_system'=>'decimal:4','qty_actual'=>'decimal:4',
        'qty_difference'=>'decimal:4','unit_cost'=>'decimal:4','total_cost'=>'decimal:4'
    ];

    public function adjustment(): BelongsTo { return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id'); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo       { return $this->belongsTo(Unit::class); }
}
