<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'category_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
      public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
     public function transaksi()
    {
        return $this->hasOne(Transaksi::class, 'cart_id');
    }
}
