<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Завантажуємо продукти з відношенням "images"
        $products = Product::with('images')->get();

        // Додаємо поле "available" на основі залишку stock
        $products->each(function ($product) {
            $product->available = $product->stock > 0;
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $existingProduct = Product::where('title', $request->title)
            ->where('description', $request->description)
            ->first();

        if ($existingProduct) {
            return response()->json($existingProduct, 200);
        }

        return Product::create($request->all());
    }

    public function show($id)
    {
        return Product::with('images')->findOrFail($id);
    }

    public function stockCheck(Request $request)
    {
        // Витягуємо масив ids з запиту
        $ids = $request->input('ids', []);

        // Валідуємо: ids має бути масивом цілих чисел
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id',
        ]);

        // Повертаємо відповідні товари з полями id, title та stock
        $products = \App\Models\Product::whereIn('id', $validated['ids'])
            ->get(['id', 'title', 'stock']);

        return response()->json($products);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json([]);
        }

        $products = Product::where('title', 'like', '%' . $query . '%')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
