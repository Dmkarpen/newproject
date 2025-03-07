<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/product-create', [ProductController::class, 'store']);
Route::post('/product-image', [ImageController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::resource('orders', OrderController::class);

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
Route::get('/me', [AuthController::class, 'me']);
