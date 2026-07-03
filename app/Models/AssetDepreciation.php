<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetDepreciation extends Model
{
    protected $fillable = ['fixed_asset_id','journal_id','year','month','amount','book_value_before','book_value_after'];
    protected $casts    = ['amount'=>'decimal:4'];

    public function fixedAsset(): BelongsTo { return $this->belongsTo(FixedAsset::class); }
    public function journal(): BelongsTo    { return $this->belongsTo(Journal::class); }
}