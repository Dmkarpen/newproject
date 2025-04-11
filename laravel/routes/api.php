<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\CheckTokenExpired;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NovaPoshtaController;
use App\Http\Controllers\WishlistController;

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/category/{name}', [ProductController::class, 'getByCategory']);
Route::post('/product-create', [ProductController::class, 'store']);
Route::post('/product-image', [ImageController::class, 'store']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products/stock-check', [ProductController::class, 'stockCheck']);
Route::get('/products-viewed', [ProductController::class, 'viewedByUser']);

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::group([
    'middleware' => [
        'auth:sanctum',
        CheckTokenExpired::class,
    ]
], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
Route::delete('/orders/{orderId}/items/{itemId}', [OrderController::class, 'removeItem']);

Route::post('/contact-messages', [ContactController::class, 'store']);

Route::get('/novaposhta/cities', [NovaPoshtaController::class, 'getCities']);
Route::post('/novaposhta/warehouses', [NovaPoshtaController::class, 'getWarehouses']);

Route::get('/wishlist', [WishlistController::class, 'index']);
Route::post('/wishlist/add', [WishlistController::class, 'add']);
Route::post('/wishlist/remove', [WishlistController::class, 'remove']);
