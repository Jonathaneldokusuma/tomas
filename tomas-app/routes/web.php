<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoritController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Admin\AdminController;

// Redirect root ke login
Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// App Pages
Route::get('/home',    [WebController::class, 'home'])->name('home');
Route::get('/riwayat', [WebController::class, 'riwayat'])->name('riwayat');
Route::get('/profile', [WebController::class, 'profile'])->name('profile');
Route::get('/tambah',  [WebController::class, 'tambah'])->name('tambah');

// Chat
Route::get('/chat',                [ChatController::class, 'index'])->name('chat');
Route::get('/chat/{id_tukang}',    [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{id_tukang}',   [ChatController::class, 'send'])->name('chat.send');

// Tukang
Route::get('/tukang',      [WebController::class, 'tukangIndex'])->name('tukang.index');
Route::get('/tukang/{id}', [WebController::class, 'tukangShow'])->name('tukang.show');

// Layanan
Route::get('/layanan/{id}', [WebController::class, 'layananShow'])->name('layanan.show');

// Order
Route::post('/orders',                    [WebController::class, 'orderStore'])->name('orders.store');
Route::post('/orders/reorder/{id_order}', [WebController::class, 'orderRestore'])->name('orders.reorder');

// Review
Route::get('/review/{id_order}',  [ReviewController::class, 'create'])->name('review.create');
Route::post('/review/{id_order}', [ReviewController::class, 'store'])->name('review.store');

// Favorit
Route::get('/favorit',         [FavoritController::class, 'index'])->name('favorit.index');
Route::post('/favorit/{id}',   [FavoritController::class, 'toggle'])->name('favorit.toggle');

// Notifikasi
Route::get('/notifikasi',        [NotifikasiController::class, 'index'])->name('notifikasi.index');
Route::get('/notifikasi/unread', [NotifikasiController::class, 'unread'])->name('notifikasi.unread');

// ── Admin ──────────────────────────────────────────────────
// Auth (no middleware)
Route::get('/admin/login',  [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout',[AdminController::class, 'logout'])->name('admin.logout');

// Protected admin routes
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/dashboard',           [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users',               [AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{id}',       [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/tukang',              [AdminController::class, 'tukang'])->name('admin.tukang');
    Route::get('/tukang/create',       [AdminController::class, 'createTukang'])->name('admin.tukang.create');
    Route::post('/tukang',             [AdminController::class, 'storeTukang'])->name('admin.tukang.store');
    Route::get('/tukang/{id}/edit',    [AdminController::class, 'editTukang'])->name('admin.tukang.edit');
    Route::put('/tukang/{id}',         [AdminController::class, 'updateTukang'])->name('admin.tukang.update');
    Route::delete('/tukang/{id}',      [AdminController::class, 'deleteTukang'])->name('admin.tukang.delete');
    Route::get('/layanan',             [AdminController::class, 'layanan'])->name('admin.layanan');
    Route::post('/layanan',            [AdminController::class, 'storeLayanan'])->name('admin.layanan.store');
    Route::put('/layanan/{id}',        [AdminController::class, 'updateLayanan'])->name('admin.layanan.update');
    Route::delete('/layanan/{id}',     [AdminController::class, 'deleteLayanan'])->name('admin.layanan.delete');
    Route::get('/orders',              [AdminController::class, 'orders'])->name('admin.orders');
    Route::delete('/orders/{id}',      [AdminController::class, 'deleteOrder'])->name('admin.orders.delete');
    Route::get('/reviews',             [AdminController::class, 'reviews'])->name('admin.reviews');
    Route::delete('/reviews/{id}',     [AdminController::class, 'deleteReview'])->name('admin.reviews.delete');

    // Tukang verifikasi & ban
    Route::get('/tukang/verifikasi',        [AdminController::class, 'verifikasiTukang'])->name('admin.tukang.verifikasi');
    Route::post('/tukang/{id}/approve',     [AdminController::class, 'approveTukang'])->name('admin.tukang.approve');
    Route::post('/tukang/{id}/reject',      [AdminController::class, 'rejectTukang'])->name('admin.tukang.reject');
    Route::post('/tukang/{id}/ban',         [AdminController::class, 'banTukang'])->name('admin.tukang.ban');
    Route::post('/tukang/{id}/unban',       [AdminController::class, 'unbanTukang'])->name('admin.tukang.unban');

    // User ban
    Route::post('/users/{id}/ban',          [AdminController::class, 'banUser'])->name('admin.users.ban');
    Route::post('/users/{id}/unban',        [AdminController::class, 'unbanUser'])->name('admin.users.unban');

    // Monitoring pembayaran
    Route::get('/pembayaran',               [AdminController::class, 'pembayaran'])->name('admin.pembayaran');
    Route::post('/pembayaran/{id}/konfirmasi', [AdminController::class, 'konfirmasiPembayaran'])->name('admin.pembayaran.konfirmasi');

    // Broadcast pesan ke tukang
    Route::get('/broadcast',                [AdminController::class, 'broadcast'])->name('admin.broadcast');
    Route::post('/broadcast',               [AdminController::class, 'storeBroadcast'])->name('admin.broadcast.store');
    Route::delete('/broadcast/{id}',        [AdminController::class, 'deleteBroadcast'])->name('admin.broadcast.delete');
});
