<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'merk',
        'harga',
        'stok',
        'subcategory_id',
        'image',
    ];

    public function details()
    {
        return $this->hasMany(ProductDetail::class, 'product_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductSubcategory::class, 'subcategory_id');
    }
    public function getImageUrlAttribute()
{
    return $this->image 
        ? asset('storage/' . $this->image) 
        : null;
}

protected $appends = ['image_url'];
   public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
      public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'product_id');
    }
}
