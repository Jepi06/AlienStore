<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            // simpan ke disk public -> storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path; // e.g., products/xxxx.jpg
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
            // hapus file lama di disk public
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // simpan file baru ke disk public
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
