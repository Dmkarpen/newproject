<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\PersonalAccessToken;

class ReviewController extends Controller
{
    // Збереження відгуку
    public function store(Request $request)
    {
        // 🔐 Авторизація через токен
        $token = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        $review = Review::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $validated['product_id']],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        return response()->json(['message' => 'Review saved successfully', 'review' => $review], 201);
    }

    // Отримати відгуки по конкретному товару
    public function indexByProduct($productId)
    {
        $product = Product::findOrFail($productId);

        $reviews = Review::where('product_id', $productId)
            ->with('user:id,name') // якщо хочемо показати ім’я автора
            ->orderByDesc('created_at')
            ->get();

        $averageRating = Review::where('product_id', $productId)->avg('rating');

        return response()->json([
            'average_rating' => round($averageRating, 1),
            'total_reviews'  => $reviews->count(),
            'reviews'        => $reviews,
        ]);
    }
}
