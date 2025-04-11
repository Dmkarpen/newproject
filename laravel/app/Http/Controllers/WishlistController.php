<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return response()->json([], 400);
        }

        $wishlist = Wishlist::where('user_id', $userId)
            ->with('product.images')
            ->get()
            ->pluck('product');

        return response()->json($wishlist);
    }

    public function add(Request $request)
    {
        $userId = $request->input('user_id');
        $productId = $request->input('product_id');

        if (!$userId || !$productId) {
            return response()->json(['message' => 'Missing data'], 400);
        }

        Wishlist::updateOrCreate([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return response()->json(['status' => 'added']);
    }

    public function remove(Request $request)
    {
        $userId = $request->input('user_id');
        $productId = $request->input('product_id');

        if (!$userId || !$productId) {
            return response()->json(['message' => 'Missing data'], 400);
        }

        Wishlist::where([
            'user_id' => $userId,
            'product_id' => $productId,
        ])->delete();

        return response()->json(['status' => 'removed']);
    }
}
