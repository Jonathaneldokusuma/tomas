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
use App\Http\Controllers\Api\TukangAuthController;
use App\Http\Controllers\Api\TukangDashboardController;
use App\Http\Controllers\Api\FcmTokenController;

// ── Public ──────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Tukang Auth
Route::post('/tukang/register', [TukangAuthController::class, 'register']);
Route::post('/tukang/login',    [TukangAuthController::class, 'login']);

Route::get('/tukang',            [TukangController::class, 'index']);
Route::get('/tukang/by-layanan', [TukangController::class, 'byLayanan']);
Route::apiResource('/layanan',   LayananController::class)->only(['index', 'show']);

// ── Tukang Dashboard (token-based) ──────────────────────────
Route::prefix('tukang')->group(function () {
    Route::get('/orders',                       [TukangDashboardController::class, 'orders']);
    Route::get('/orders/pending',               [TukangDashboardController::class, 'pendingOrders']);
    Route::post('/orders/{id}/accept',          [TukangDashboardController::class, 'acceptOrder']);
    Route::post('/orders/{id}/reject',          [TukangDashboardController::class, 'rejectOrder']);
    Route::post('/orders/{id}/status',          [TukangDashboardController::class, 'updateStatus']);
    Route::post('/orders/{id}/confirm-payment', [TukangDashboardController::class, 'confirmPayment']);
    Route::get('/profile',                      [TukangDashboardController::class, 'profile']);
    Route::put('/profile',                      [TukangDashboardController::class, 'updateProfile']);
    Route::post('/upload-ktp',                  [TukangDashboardController::class, 'uploadKtp']);

    // Chat tukang ↔ pelanggan
    Route::get('/chat',            [TukangDashboardController::class, 'chatInbox']);
    Route::get('/chat/{id_user}',  [TukangDashboardController::class, 'chatMessages']);
    Route::post('/chat/{id_user}', [TukangDashboardController::class, 'chatSend']);

    // Pesan dari pusat (admin broadcast)
    Route::get('/broadcast', [TukangDashboardController::class, 'broadcast']);

    // FCM token (tukang)
    Route::post('/fcm-token', [FcmTokenController::class, 'storeTukang']);
});

// Wildcard route tukang - HARUS setelah semua specific routes
Route::get('/tukang/{id}', [TukangController::class, 'show']);

// ── Protected (Sanctum) ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/me',       [AuthController::class, 'me']);
    Route::put('/profile',  [AuthController::class, 'updateProfile']);

    Route::get('/orders',                       [OrderController::class, 'index']);
    Route::post('/orders',                      [OrderController::class, 'store']);
    Route::get('/orders/{id}',                  [OrderController::class, 'show']);
    Route::post('/orders/{id}/upload-bukti',    [OrderController::class, 'uploadBuktiBayar']);

    Route::post('/reviews/{id_order}', [ReviewController::class, 'store']);

    Route::get('/favorit',                   [FavoritController::class, 'index']);
    Route::post('/favorit/{id_tukang}',      [FavoritController::class, 'toggle']);
    Route::get('/favorit/{id_tukang}/check', [FavoritController::class, 'check']);

    Route::get('/chat',             [ChatController::class, 'index']);
    Route::get('/chat/{id_tukang}', [ChatController::class, 'show']);
    Route::post('/chat/{id_tukang}',[ChatController::class, 'send']);

    Route::post('/pembayaran/{id_order}/pay',   [PembayaranController::class, 'pay']);
    Route::get('/pembayaran/{id_order}/status', [PembayaranController::class, 'status']);

    // ── Notifikasi - specific routes HARUS sebelum wildcard {id} ──
    Route::get('/notifikasi',              [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'unreadCount']);
    Route::put('/notifikasi/read-all',     [NotifikasiController::class, 'markAllRead']);
    Route::put('/notifikasi/{id}/read',    [NotifikasiController::class, 'markRead']);

    // FCM token (user)
    Route::post('/fcm-token', [FcmTokenController::class, 'storeUser']);
});
