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
            'qty'        => 'nullable|integer|min:1', // qty hanya untuk product langsung
        ]);

        $grossAmount = 0;

        if (!empty($validated['cart_id'])) {
            // Dari cart
            $cart = Cart::with('items.product')->findOrFail($validated['cart_id']);
            $grossAmount = $cart->items->sum(function ($item) {
                return $item->product->harga * $item->qty;
            });
        } elseif (!empty($validated['product_id'])) {
            // Dari produk langsung
            $product = Product::findOrFail($validated['product_id']);
            $qty = $validated['qty'] ?? 1;
            $grossAmount = $product->harga * $qty;
        } else {
            return response()->json(['message' => 'Cart atau Product harus diisi'], 422);
        }

        // order_id unik
        $orderId = 'TRX-' . time() . '-' . $request->user()->id;

        // parameter transaksi Midtrans
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

        // Buat transaksi Midtrans
        $snapTransaction = Snap::createTransaction($params);

        // Simpan ke database
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

        // Jika dari cart → update status jadi checkedout & kurangi stok produk
        if ($transaksi->cart_id) {
            foreach ($cart->items as $item) {
                $item->product->decrement('stok', $item->qty);
            }
            $transaksi->cart()->update(['status' => 'checkedout']);
        } elseif ($transaksi->product_id) {
            // Dari produk langsung → kurangi stok produk
            $product->decrement('stok', $validated['qty'] ?? 1);
        }

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

        if ($transaksi) {
            $transaksi->update([
                'status'           => $notif->transaction_status,
                'payment_type'     => $notif->payment_type,
                'transaction_time' => $notif->transaction_time,
            ]);

            // Kalau berhasil dibayar, update cart jadi "paid"
            if ($notif->transaction_status === 'settlement' && $transaksi->cart_id) {
                $transaksi->cart()->update(['status' => 'paid']);
            }
        }

        return response()->json(['message' => 'Callback processed']);
    }
}
