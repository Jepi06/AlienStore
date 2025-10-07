<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;
use Midtrans\Notification;

class TransaksiController extends Controller
{
    /**
     * Buat transaksi Midtrans dan kembalikan URL Snap
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart_id'    => 'nullable|exists:carts,id',
            'product_id' => 'nullable|exists:products,id',
            'qty'        => 'nullable|integer|min:1',
        ]);

        if (empty($validated['cart_id']) && empty($validated['product_id'])) {
            return response()->json(['message' => 'Cart atau Product harus diisi'], 422);
        }

        // Hitung total harga
        $grossAmount = 0;

        if (!empty($validated['cart_id'])) {
            $cart = Cart::with('items.product')->findOrFail($validated['cart_id']);
            $grossAmount = $cart->items->sum(fn($item) => $item->product->harga * $item->qty);
        } else {
            $product = Product::findOrFail($validated['product_id']);
            $qty = $validated['qty'] ?? 1;
            $grossAmount = $product->harga * $qty;
        }

        // Buat order ID unik
        $orderId = 'TRX-' . time() . '-' . $request->user()->id;

        // Setup parameter transaksi Midtrans
        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $request->user()->name,
                'email'      => $request->user()->email,
            ],
            // callback otomatis tanpa daftar di dashboard Midtrans
            'callbacks' => [
                'notification_url' => url('/api/midtrans/callback'),
            ],
        ];

        try {
            $snapTransaction = Snap::createTransaction($params);
        } catch (\Exception $e) {
            Log::error('Gagal membuat transaksi Midtrans:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal membuat transaksi Midtrans', 'error' => $e->getMessage()], 500);
        }

        // Simpan transaksi ke database
        $transaksi = Transaksi::create([
            'user_id'      => $request->user()->id,
            'cart_id'      => $validated['cart_id'] ?? null,
            'product_id'   => $validated['product_id'] ?? null,
            'qty'          => $validated['qty'] ?? null,
            'gross_amount' => $grossAmount,
            'status'       => 'pending',
            'order_id'     => $orderId,
            'snap_token'   => $snapTransaction->token ?? null,
        ]);

        return response()->json([
            'message'      => 'Transaksi berhasil dibuat',
            'transaksi'    => $transaksi,
            'redirect_url' => $snapTransaction->redirect_url ?? null,
        ]);
    }

    /**
     * Callback otomatis dari Midtrans (tidak perlu daftar manual)
     */
    public function callback(Request $request)
    {
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            Log::error('Gagal memproses notifikasi Midtrans:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal memproses callback'], 400);
        }

        $orderId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $paymentType = $notif->payment_type;
        $transactionTime = $notif->transaction_time;

        Log::info('Callback diterima dari Midtrans:', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
            'payment_type' => $paymentType,
        ]);

        $transaksi = Transaksi::where('order_id', $orderId)->first();

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $oldStatus = $transaksi->status;
        $transaksi->update([
            'status'           => $transactionStatus,
            'payment_type'     => $paymentType,
            'transaction_time' => $transactionTime,
        ]);

        // Jika pembayaran sukses (capture/settlement)
        if (in_array($transactionStatus, ['settlement', 'capture']) && $oldStatus !== 'settlement') {

            if ($transaksi->cart_id) {
                $cart = Cart::with('items.product')->find($transaksi->cart_id);
                if ($cart && $cart->items->count() > 0) {
                    foreach ($cart->items as $item) {
                        $item->product->decrement('stok', $item->qty);
                    }
                    $cart->update(['status' => 'paid']);
                }
            } elseif ($transaksi->product_id) {
                $product = Product::find($transaksi->product_id);
                if ($product) {
                    $product->decrement('stok', $transaksi->qty ?? 1);
                }
            }

            Log::info('Transaksi sukses, stok dikurangi', ['order_id' => $orderId]);
        }

        // Jika transaksi gagal / dibatalkan / kadaluarsa
        if (in_array($transactionStatus, ['cancel', 'expire', 'deny'])) {
            if ($transaksi->cart_id) {
                $transaksi->cart()->update(['status' => 'cancelled']);
            }
            Log::warning('Transaksi gagal atau dibatalkan', ['order_id' => $orderId]);
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }
}
