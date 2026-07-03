<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactGroup extends Model
{
    protected $fillable = ['company_id','name','type'];
    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function contacts(): HasMany   { return $this->hasMany(Contact::class); }
}