<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AccountCategory extends Model
{
    protected $fillable = ['code','name','type','is_debit_normal'];
    protected $casts    = ['is_debit_normal'=>'boolean'];
    public function accounts(): HasMany { return $this->hasMany(Account::class); }
}
