<?php

use App\Http\Controllers\Seller\SellerPortalController;
use App\Http\Controllers\Seller\AnalyticsInsightController;
use App\Http\Controllers\Seller\CustomerController;
use App\Http\Controllers\Seller\ReportNoteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('seller.dashboard');
});

// Admin login routes (simple controller)
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::redirect('/seller', '/penjual');

Route::prefix('penjual')->name('seller.')->group(function () {
    Route::get('/login', [SellerPortalController::class, 'login'])->name('login');
    Route::post('/login', [SellerPortalController::class, 'authenticate'])->name('authenticate');

    Route::middleware('seller.portal')->group(function () {
        Route::post('/logout', [SellerPortalController::class, 'logout'])->name('logout');
        Route::get('/', [SellerPortalController::class, 'dashboard'])->name('dashboard');

        // Product CRUD for seller portal
        Route::resource('/produk', \App\Http\Controllers\Seller\ProductController::class)->parameters(['produk' => 'product'])->names([
            'index' => 'products',
            'create' => 'products.create',
            'store' => 'products.store',
            'show' => 'products.show',
            'edit' => 'products.edit',
            'update' => 'products.update',
            'destroy' => 'products.destroy',
        ]);

        // Product images: upload, delete, reorder (AJAX)
        Route::post('/produk/{product}/images', [\App\Http\Controllers\Seller\ProductImageController::class, 'store'])->name('products.images.store');
        Route::delete('/produk/images/{image}', [\App\Http\Controllers\Seller\ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::post('/produk/{product}/images/reorder', [\App\Http\Controllers\Seller\ProductImageController::class, 'reorder'])->name('products.images.reorder');

        // Category CRUD for seller portal
        Route::resource('/kategori', \App\Http\Controllers\Seller\CategoryController::class)->parameters(['kategori' => 'category'])->names([
            'index' => 'categories',
            'create' => 'categories.create',
            'store' => 'categories.store',
            'show' => 'categories.show',
            'edit' => 'categories.edit',
            'update' => 'categories.update',
            'destroy' => 'categories.destroy',
        ]);
        Route::get('/pesanan', [SellerPortalController::class, 'orders'])->name('orders');
        Route::patch('/pesanan/{order}/status', [SellerPortalController::class, 'updateOrderStatus'])->name('orders.update-status');
        Route::delete('/pesanan/{order}', [SellerPortalController::class, 'destroyOrder'])->name('orders.destroy');
        Route::resource('/pelanggan', CustomerController::class)->parameters(['pelanggan' => 'pelanggan'])->names([
            'index' => 'customers',
            'create' => 'customers.create',
            'store' => 'customers.store',
            'edit' => 'customers.edit',
            'update' => 'customers.update',
            'destroy' => 'customers.destroy',
        ])->except(['show']);
        Route::get('/ulasan', [SellerPortalController::class, 'reviews'])->name('reviews');
        Route::get('/laporan', [SellerPortalController::class, 'reports'])->name('reports');
        Route::post('/laporan', [ReportNoteController::class, 'store'])->name('reports.store');
        Route::put('/laporan/{laporan}', [ReportNoteController::class, 'update'])->name('reports.update');
        Route::delete('/laporan/{laporan}', [ReportNoteController::class, 'destroy'])->name('reports.destroy');
        Route::get('/analytics', [SellerPortalController::class, 'analytics'])->name('analytics');
        Route::post('/analytics', [AnalyticsInsightController::class, 'store'])->name('analytics.store');
        Route::put('/analytics/{insight}', [AnalyticsInsightController::class, 'update'])->name('analytics.update');
        Route::delete('/analytics/{insight}', [AnalyticsInsightController::class, 'destroy'])->name('analytics.destroy');
        Route::get('/pengaturan', [SellerPortalController::class, 'settings'])->name('settings');
        Route::match(['post', 'patch'], '/pengaturan', [SellerPortalController::class, 'updateSettings'])->name('settings.update');
    });
});
