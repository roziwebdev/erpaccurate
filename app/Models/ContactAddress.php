<?php
// app/Models/ContactAddress.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactAddress extends Model
{
    protected $fillable = ['contact_id','label','address','city','province','postal_code','country','is_default'];
    protected $casts    = ['is_default'=>'boolean'];
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
}