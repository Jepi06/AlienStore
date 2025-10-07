<?php

namespace App\Http\Controllers;

use App\Models\ProductSubcategory;
use Illuminate\Http\Request;

class ProductSubcategoryController extends Controller
{
    public function index()
    {
        return response()->json(ProductSubcategory::with('category')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        $subcategory = ProductSubcategory::create($validated);
        return response()->json($subcategory, 201);
    }

    public function show($id)
    {
        $subcategory = ProductSubcategory::with('category', 'products')->findOrFail($id);
        return response()->json($subcategory);
    }

    public function update(Request $request, $id)
    {
        $subcategory = ProductSubcategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        $subcategory->update($validated);
        return response()->json($subcategory);
    }

    public function destroy($id)
    {
        $subcategory = ProductSubcategory::findOrFail($id);
        $subcategory->delete();

        return response()->json(['message' => 'Subkategori berhasil dihapus']);
    }
}
