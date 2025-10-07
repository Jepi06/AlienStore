<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;
use Midtrans\Transaction;

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
            // redirect setelah selesai pembayaran
            'callbacks' => [
                'finish' => url('api/payment/success/' . $orderId),
            ],
        ];

        try {
            $snapTransaction = Snap::createTransaction($params);
        } catch (\Exception $e) {
            Log::error('Gagal membuat transaksi Midtrans', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal membuat transaksi Midtrans',
                'error'   => $e->getMessage(),
            ], 500);
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
     * Cek status transaksi langsung ke Midtrans (tanpa callback)
     */
 public function paymentSuccess($orderId, Request $request)
{
    $transaksi = Transaksi::where('order_id', $orderId)->first();

    if (!$transaksi) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    try {
        // Ambil status langsung dari Midtrans
        $status = \Midtrans\Transaction::status($orderId);
        $transactionStatus = $status->transaction_status ?? 'unknown';
        $paymentType = $status->payment_type ?? null;
        $transactionTime = $status->transaction_time ?? now();

        // Update status transaksi di DB
        $transaksi->update([
            'status' => $transactionStatus,
            'payment_type' => $paymentType,
            'transaction_time' => $transactionTime,
        ]);

        // Jika pembayaran sukses, kurangi stok
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
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

        // Jika transaksi gagal, expired, atau dibatalkan
        if (in_array($transactionStatus, ['cancel', 'expire', 'deny'])) {
            if ($transaksi->cart_id) {
                $transaksi->cart()->update(['status' => 'cancelled']);
            }
            Log::warning('Transaksi gagal atau dibatalkan', ['order_id' => $orderId]);
        }

        return response()->json([
            'message' => 'Status transaksi diperbarui',
            'order_id' => $transaksi->order_id,
            'status' => $transactionStatus,
            'midtrans_response' => $status,
        ]);
    } catch (\Exception $e) {
        Log::error('Gagal mengambil status dari Midtrans', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Gagal mengambil status dari Midtrans'], 500);
    }
}
  public function index()
    {
        $transaksis = Transaksi::with(['product','user'])->get();
        return response()->json($transaksis);
    }
     public function show($id)
    {
        $transaksi = Transaksi::with(['product','user'])->find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json($transaksi);
    }
    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $transaksi->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }
}
