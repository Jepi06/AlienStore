<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['details', 'subcategory'])->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'merk' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'subcategory_id' => 'required|exists:product_subcategories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // generate nama file hash unik
            $filename = $request->file('image')->hashName();
            $request->file('image')->move(public_path('storage/products'), $filename);
            $validated['image'] = 'products/' . $filename;
        }

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load(['details', 'subcategory']));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'merk' => 'sometimes|required|string|max:255',
            'harga' => 'sometimes|required|numeric',
            'stok' => 'sometimes|required|integer',
            'subcategory_id' => 'sometimes|required|exists:product_subcategories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // hapus file lama
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            // generate nama file hash unik
            $filename = $request->file('image')->hashName();
            $request->file('image')->move(public_path('storage/products'), $filename);
            $validated['image'] = 'products/' . $filename;
        }

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
