<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class CompanySetting extends Model
{
    protected $fillable = ['company_id','key','value'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}