<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReceipt extends Model
{
    use SoftDeletes;
    protected $fillable = ['company_id','contact_id','created_by','number','date','status','reference','notes','amount','currency_code','exchange_rate','account_id'];
    protected $casts    = ['date'=>'date','amount'=>'decimal:4'];

    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function contact(): BelongsTo  { return $this->belongsTo(Contact::class); }
    public function account(): BelongsTo  { return $this->belongsTo(Account::class); }
    public function createdBy(): BelongsTo{ return $this->belongsTo(User::class,'created_by'); }
    public function items(): HasMany      { return $this->hasMany(SalesReceiptItem::class); }
}