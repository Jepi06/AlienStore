<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with(['items.product', 'category'])->where('user_id', auth()->id())->get();
        return response()->json($carts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_detail_id' => 'nullable|exists:product_details,id',
            'qty' => 'required|integer|min:1',
        ]);

        $userId = auth()->id();
        $product = Product::findOrFail($request->product_id);
        $categoryId = $product->subcategory->category_id;

        // cari cart user berdasarkan kategori
        $cart = Cart::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
            ]);
        }

        // tambahkan produk ke cart_items
        $cart->items()->create([
            'product_id' => $product->id,
            'product_detail_id' => $request->product_detail_id,
            'qty' => $request->qty,
            'price' => $product->harga,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart' => $cart->load('items.product', 'category'),
        ], 201);
    }

    public function show(Cart $cart)
    {
        $this->authorize('view', $cart);
        return response()->json($cart->load('items.product', 'category'));
    }

    public function update(Request $request, Cart $cart)
    {
        $this->authorize('update', $cart);

        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $item = $cart->items()->where('product_id', $request->product_id)->firstOrFail();
        $item->update(['qty' => $request->qty]);

        return response()->json(['message' => 'Cart updated', 'cart' => $cart->load('items')]);
    }

   public function destroy(Cart $cart)
{
    $user = auth()->user();

    // Pastikan cart milik user yang login
    if ($cart->user_id !== $user->id) {
        return response()->json(['message' => 'Anda tidak memiliki izin menghapus cart ini.'], 403);
    }

    $cart->delete();
    return response()->json(['message' => 'Cart deleted']);
}

}
