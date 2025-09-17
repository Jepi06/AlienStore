<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function index(Product $product)
    {

        $details = Product::with('details')->get();
        return response()->json($details);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'warna' => 'required|string',
            'ukuran' => 'required|string',
            'bahan' => 'nullable|string',
             'product_id' => 'required|exists:products,id',
        ]);

        // tambahkan product_id secara otomatis

        $detail = ProductDetail::create($validated);

        return response()->json($detail, 201);
    }

    public function show(Product $product, ProductDetail $detail)
    {
        if ($detail->product_id !== $product->id) {
            return response()->json(['error' => 'Detail not found for this product'], 404);
        }
        return response()->json($detail);
    }

    public function update(Request $request, Product $product, ProductDetail $detail)
    {
        if ($detail->product_id !== $product->id) {
            return response()->json(['error' => 'Detail not found for this product'], 404);
        }

        $validated = $request->validate([
            'warna' => 'required|string',
            'ukuran' => 'required|string',
            'bahan' => 'nullable|string',
             'product_id' => 'required|exists:products,id',
        ]);

        // pastikan product_id tetap konsisten
        $validated['product_id'] = $product->id;

        $detail->update($validated);

        return response()->json($detail, 200);
    }

    public function destroy(Product $product, ProductDetail $detail)
    {
        if ($detail->product_id !== $product->id) {
            return response()->json(['error' => 'Detail not found for this product'], 404);
        }

        $detail->delete();

        return response()->json(null, 204);
    }
}
