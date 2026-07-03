<?php
// app/Models/StockAdjustment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id','warehouse_id','created_by','number','date',
        'status','reference','notes','type','account_id'
    ];
    protected $casts = ['date' => 'date'];

    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function account(): BelongsTo   { return $this->belongsTo(Account::class); }
    public function items(): HasMany       { return $this->hasMany(StockAdjustmentItem::class); }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'draft' => 'secondary', 'posted' => 'success', 'cancelled' => 'danger', default => 'secondary'
        };
    }
    public function getTypeLabelAttribute(): string {
        return match($this->type) {
            'increase' => 'Penambahan', 'decrease' => 'Pengurangan', 'opname' => 'Opname Stok', default => '-'
        };
    }
}