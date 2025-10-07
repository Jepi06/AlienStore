<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\SecRoleController;
use App\Http\Controllers\Admin\SecUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductSubcategoryController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\TransaksiController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware(['auth:api'])->group(function () {

    // hanya Admin yang boleh admin
    Route::apiResource('/role', SecRoleController::class)->middleware('role:Admin');
    Route::apiResource('/user', SecUserController::class)->middleware('role:Admin');

    // hanya Admin yang boleh kelola produk
    Route::apiResource('products', ProductController::class)->middleware('role:Admin');
    Route::apiResource('products-detail', ProductDetailController::class)->middleware('role:Admin');
    // kategori & subkategori juga bisa dibatasi
    Route::apiResource('categories', ProductCategoryController::class)->middleware('role:Admin');
    Route::apiResource('subcategories', ProductSubcategoryController::class)->middleware('role:Admin');
    // hanya user
    Route::get('/user-login-products', [ProductController::class,'index'])->middleware('role:User');
    Route::get('/user-login-categories', [ProductCategoryController::class,'index'])->middleware('role:User');
    Route::get('/user-login-subcategories', [ProductSubcategoryController::class,'index'])->middleware('role:User');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->middleware('role:User');
    Route::apiResource('carts', CartController::class)->middleware('role:User');
});
Route::post('/midtrans/callback', [TransaksiController::class, 'callback']);
Route::get('/user-products', [ProductController::class,'index']);
Route::get('/user-categories', [ProductCategoryController::class,'index']);
Route::get('/user-subcategories', [ProductSubcategoryController::class,'index']);
Route::post('/register', [AuthController::class, 'Register']);
Route::post('/login', [AuthController::class, 'Login']);
