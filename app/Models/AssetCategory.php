<?php

// app/Models/AssetCategory.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    protected $fillable = ['company_id','name','useful_life','depreciation_rate','depreciation_method','asset_account_id','depreciation_account_id','accumulated_account_id'];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function assets(): HasMany       { return $this->hasMany(FixedAsset::class); }
    public function assetAccount(): BelongsTo       { return $this->belongsTo(Account::class,'asset_account_id'); }
    public function depreciationAccount(): BelongsTo{ return $this->belongsTo(Account::class,'depreciation_account_id'); }
    public function accumulatedAccount(): BelongsTo { return $this->belongsTo(Account::class,'accumulated_account_id'); }
}
