<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index']);
Route::post('/product-create', [ProductController::class, 'store']);
Route::post('/product-image', [ImageController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::resource('orders', OrderController::class);
