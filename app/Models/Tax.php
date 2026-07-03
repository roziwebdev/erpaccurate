<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $fillable = ['company_id','code','name','rate','type','sales_account_id','purchase_account_id','is_active'];
    protected $casts    = ['rate'=>'decimal:4','is_active'=>'boolean'];

    public function company(): BelongsTo        { return $this->belongsTo(Company::class); }
    public function salesAccount(): BelongsTo   { return $this->belongsTo(Account::class,'sales_account_id'); }
    public function purchaseAccount(): BelongsTo{ return $this->belongsTo(Account::class,'purchase_account_id'); }
}