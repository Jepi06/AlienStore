<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Notification;

class TransaksiController extends Controller
{
    /**
     * Buat transaksi dan kembalikan URL Midtrans
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'cart_id'    => 'nullable|exists:carts,id',
        'product_id' => 'nullable|exists:products,id',
        'qty'        => 'nullable|integer|min:1',
    ]);

    $grossAmount = 0;

    if (!empty($validated['cart_id'])) {
        $cart = Cart::with('items.product')->findOrFail($validated['cart_id']);
        $grossAmount = $cart->items->sum(fn($item) => $item->product->harga * $item->qty);
    } elseif (!empty($validated['product_id'])) {
        $product = Product::findOrFail($validated['product_id']);
        $qty = $validated['qty'] ?? 1;
        $grossAmount = $product->harga * $qty;
    } else {
        return response()->json(['message' => 'Cart atau Product harus diisi'], 422);
    }

    $orderId = 'TRX-' . time() . '-' . $request->user()->id;

    $params = [
        'transaction_details' => [
            'order_id'     => $orderId,
            'gross_amount' => $grossAmount,
        ],
        'customer_details' => [
            'first_name' => $request->user()->name,
            'email'      => $request->user()->email,
        ],
    ];

    $snapTransaction = Snap::createTransaction($params);

    // Simpan transaksi (belum ubah stok!)
    $transaksi = Transaksi::create([
        'user_id'      => $request->user()->id,
        'cart_id'      => $validated['cart_id'] ?? null,
        'product_id'   => $validated['product_id'] ?? null,
        'qty'          => $validated['qty'] ?? null,
        'gross_amount' => $grossAmount,
        'status'       => 'pending',
        'order_id'     => $orderId,
        'snap_token'   => $snapTransaction->token,
    ]);

    return response()->json([
        'transaksi'    => $transaksi,
        'redirect_url' => $snapTransaction->redirect_url,
    ]);
}

    /**
     * Callback dari Midtrans untuk update status transaksi
     */
 public function callback(Request $request)
{
    $notif = new Notification();
    $transaksi = Transaksi::where('order_id', $notif->order_id)->first();

    if (!$transaksi) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    $transaksi->update([
        'status'           => $notif->transaction_status,
        'payment_type'     => $notif->payment_type,
        'transaction_time' => $notif->transaction_time,
    ]);

    // Jika transaksi sukses (settlement)
    if ($notif->transaction_status === 'settlement') {
        if ($transaksi->cart_id) {
            $cart = $transaksi->cart()->with('items.product')->first();
            foreach ($cart->items as $item) {
                $item->product->decrement('stok', $item->qty);
            }
            $cart->update(['status' => 'paid']);
        } elseif ($transaksi->product_id) {
            $product = Product::find($transaksi->product_id);
            $product->decrement('stok', $transaksi->qty ?? 1);
        }
    }

    // Jika transaksi dibatalkan, expired, atau gagal
    if (in_array($notif->transaction_status, ['cancel', 'expire', 'deny'])) {
        if ($transaksi->cart_id) {
            $transaksi->cart()->update(['status' => 'cancelled']);
        }
    }

    return response()->json(['message' => 'Callback processed']);
}
}