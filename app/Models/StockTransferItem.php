<?php
// app/Models/StockTransferItem.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = ['stock_transfer_id','product_id','unit_id','description','quantity','unit_cost','sort_order'];
    protected $casts    = ['quantity'=>'decimal:4','unit_cost'=>'decimal:4'];

    public function transfer(): BelongsTo { return $this->belongsTo(StockTransfer::class, 'stock_transfer_id'); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo     { return $this->belongsTo(Unit::class); }
}