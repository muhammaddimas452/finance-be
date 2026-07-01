<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

// --- RUTE PUBLIK (Bisa diakses siapa saja tanpa token) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- RUTE TERPROTEKSI (Wajib bawa Token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Tambahkan di dalam Route::middleware('auth:sanctum')->group(function () { ... });
    Route::patch('/wallets/{id}/set-primary', [App\Http\Controllers\WalletController::class, 'setPrimary']);

    // Tambahkan di dalam Route::middleware('auth:sanctum')->group(function () { ...
    Route::apiResource('bills', App\Http\Controllers\BillController::class);

    Route::post('/profile', [AuthController::class, 'updateProfile']);
    // Logout (menghapus token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Mengambil data profile user yang sedang login
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::post('/wallets/transfer', [WalletController::class, 'transfer']);
    Route::apiResource('wallets', WalletController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
});
