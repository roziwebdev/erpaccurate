<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['company_id','role_id','name','email','email_verified_at','password','phone','avatar','is_active','remember_token'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function role(): BelongsTo    { return $this->belongsTo(Role::class); }
}

