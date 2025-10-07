<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'transaksi';
    protected $fillable = [
        'user_id',
        'cart_id',
        'product_id',
        'gross_amount',
        'status',
        'payment_type',
        'order_id',
        'snap_token',
        'transaction_time',
        'qty'
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke cart (jika checkout keranjang)
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Relasi ke produk (jika beli langsung)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
