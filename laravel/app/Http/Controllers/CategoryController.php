<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CategoryController extends Controller
{
    // Отримати всі доступні категорії
    public function index()
    {
        $categories = Product::distinct()->pluck('category');
        return response()->json($categories);
    }

    // Отримати продукти за категорією
    public function getByCategory($category)
    {
        $products = Product::where('category', $category)->get();
        return response()->json([
            'products' => $products
        ]);
    }
}
