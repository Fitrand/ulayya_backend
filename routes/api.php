<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Seller\AnalyticsInsightController as SellerAnalyticsInsightController;
use App\Http\Controllers\Api\Seller\CustomerController as SellerCustomerController;
use App\Http\Controllers\Api\Seller\ReportNoteController as SellerReportNoteController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreProfileController;
use App\Http\Controllers\Api\WishlistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password-otp', [AuthController::class, 'resetPasswordWithOtp']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/store-profile', [StoreProfileController::class, 'show']);

    // Midtrans webhook — publik, tidak butuh auth (di-hit Midtrans server)
    Route::post('/midtrans/notification', [MidtransController::class, 'notification']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('/auth/password', [AuthController::class, 'changePassword']);
        Route::post('/auth/avatar', [AuthController::class, 'uploadAvatar']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::patch('/cart/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}/confirm-delivered', [OrderController::class, 'confirmDelivered']);
        Route::patch('/orders/{order}/complete', [OrderController::class, 'completeOrder']);
        Route::get('/orders/{order}/check-status', [OrderController::class, 'checkPaymentStatus']);
        Route::post('/shipping/calculate', [OrderController::class, 'calculateShipping']);

        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{addressId}', [AddressController::class, 'update']);
        Route::delete('/addresses/{addressId}', [AddressController::class, 'destroy']);

        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist/{product}', [WishlistController::class, 'toggle']);

        Route::post('/orders/{order}/items/{item}/review', [ProductReviewController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'admin.api'])->prefix('/seller')->group(function () {
        Route::apiResource('/customers', SellerCustomerController::class);
        Route::apiResource('/reports', SellerReportNoteController::class);
        Route::apiResource('/analytics', SellerAnalyticsInsightController::class)->parameters([
            'analytics' => 'insight',
        ]);
    });
});
