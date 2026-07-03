<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalPeriod extends Model
{
    protected $fillable = ['company_id','year','month','start_date','end_date','status'];
    protected $casts    = ['start_date'=>'date','end_date'=>'date'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}