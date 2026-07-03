<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use SoftDeletes;
    protected $fillable = ['company_id','asset_category_id','warehouse_id','code','name','description','acquisition_date','acquisition_cost','salvage_value','book_value','accumulated_depreciation','useful_life','depreciation_rate','depreciation_method','status','disposal_date','disposal_amount'];
    protected $casts    = ['acquisition_date'=>'date','disposal_date'=>'date','acquisition_cost'=>'decimal:4','book_value'=>'decimal:4'];

    public function company(): BelongsTo       { return $this->belongsTo(Company::class); }
    public function category(): BelongsTo      { return $this->belongsTo(AssetCategory::class,'asset_category_id'); }
    public function warehouse(): BelongsTo     { return $this->belongsTo(Warehouse::class); }
    public function depreciations(): HasMany   { return $this->hasMany(AssetDepreciation::class); }
}