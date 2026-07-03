<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['company_id','code','name','address','phone','is_active'];
    protected $casts    = ['is_active'=>'boolean'];

    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function stocks(): HasMany     { return $this->hasMany(ProductStock::class); }
}