<?php
// app/Models/Account.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = ['journal_id','account_id','contact_id','description','debit','credit','sort_order'];
    protected $casts    = ['debit'=>'decimal:4','credit'=>'decimal:4'];

    public function journal(): BelongsTo  { return $this->belongsTo(Journal::class); }
    public function account(): BelongsTo  { return $this->belongsTo(Account::class); }
    public function contact(): BelongsTo  { return $this->belongsTo(Contact::class); }
}