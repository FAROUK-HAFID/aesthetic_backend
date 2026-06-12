<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::post('/coupons/apply', [CouponController::class, 'apply']);
Route::post('/checkout', [OrderController::class, 'checkout']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::post('/products/{product}/images', [ProductImageController::class, 'store']);
Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy']);
Route::post('/products/{product}/images', [ProductController::class, 'uploadImages']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::get('/cart/{sessionId}', [CartController::class, 'show']);
Route::post('/cart/update', [CartController::class, 'update']);
Route::post('/cart/remove', [CartController::class, 'remove']);