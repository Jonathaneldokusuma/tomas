<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TukangController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoritController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\LayananController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\NotifikasiController;

// ── Public ──────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/tukang',              [TukangController::class, 'index']);
Route::get('/tukang/by-layanan',   [TukangController::class, 'byLayanan']);
Route::get('/tukang/{id}',         [TukangController::class, 'show']);
Route::apiResource('/layanan',      LayananController::class)->only(['index', 'show']);

// ── Protected (Sanctum) ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/me',       [AuthController::class, 'me']);
    Route::put('/profile',  [AuthController::class, 'updateProfile']);

    Route::get('/orders',          [OrderController::class, 'index']);
    Route::post('/orders',         [OrderController::class, 'store']);

    Route::post('/reviews/{id_order}', [ReviewController::class, 'store']);

    Route::get('/favorit',              [FavoritController::class, 'index']);
    Route::post('/favorit/{id_tukang}', [FavoritController::class, 'toggle']);
    Route::get('/favorit/{id_tukang}/check', [FavoritController::class, 'check']);

    Route::get('/chat',                  [ChatController::class, 'index']);
    Route::get('/chat/{id_tukang}',      [ChatController::class, 'show']);
    Route::post('/chat/{id_tukang}',     [ChatController::class, 'send']);

    Route::post('/pembayaran/{id_order}/pay',    [PembayaranController::class, 'pay']);
    Route::get('/pembayaran/{id_order}/status',  [PembayaranController::class, 'status']);

    Route::get('/notifikasi',                    [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/unread-count',        [NotifikasiController::class, 'unreadCount']);
    Route::put('/notifikasi/read-all',            [NotifikasiController::class, 'markAllRead']);
    Route::put('/notifikasi/{id}/read',           [NotifikasiController::class, 'markRead']);
});

