<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ViewedProduct;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get();

        $products->each(function ($product) {
            $product->available = $product->stock > 0;
        });
        // dd($products->first()->toArray());
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

    // public function show($id)
    // {
    //     return Product::with('images')->findOrFail($id);
    // }

    public function show(Request $request, $id)
    {
        $product = Product::with('images')->findOrFail($id);

        // 🔐 Ручна аутентифікація
        $token = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        if ($user instanceof User) {
            ViewedProduct::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $id],
                ['viewed_at' => now()]
            );
        }

        return response()->json($product);
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
        $products = Product::whereIn('id', $validated['ids'])
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

    public function getByCategory($name)
    {
        $products = Product::where('category', $name)->get();

        return response()->json([
            'products' => $products
        ]);
    }

    public function viewedByUser(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json([], 400); // або [], якщо хочеш просто повернути пустий масив
        }

        $viewed = ViewedProduct::where('user_id', $userId)
            ->orderByDesc('viewed_at')
            ->with('product.images')
            ->limit(10)
            ->get()
            ->pluck('product');

        return response()->json($viewed);
    }

    //     public function viewedByUser(Request $request)
    // {
    //     return response()->json(['status' => 'OK']);
    // }
}
