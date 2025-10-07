<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class SecUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'sec_user';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'password',
        'email',
        'role_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo(SecRole::class, 'role_id');
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
      public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'user_id');
    }
}
