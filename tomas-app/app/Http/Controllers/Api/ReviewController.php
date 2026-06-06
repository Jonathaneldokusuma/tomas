<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $id_order)
    {
        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $order = Order::with('review', 'tukang')
            ->where('id_order', $id_order)
            ->where('id_user', $user->id_user)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        if ($order->review) {
            return response()->json(['message' => 'Review sudah ada.'], 409);
        }

        try {
            $review = Review::updateOrCreate(
                ['id_order' => $order->id_order],
                [
                    'rating'   => (int) $request->rating,
                    'komentar' => $request->filled('komentar')
                        ? trim((string) $request->komentar)
                        : null,
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal menyimpan ulasan. Coba lagi.',
            ], 500);
        }

        try {
            Notifikasi::kirim(
                $user->id_user,
                'Ulasan Berhasil Dikirim',
                'Terima kasih! Ulasan ' . $request->rating . ' bintang Anda untuk ' . ($order->tukang->nama ?? 'tukang') . ' telah tersimpan.',
                'review'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Review berhasil disimpan.',
            'review'   => $review,
        ], 201);
    }
}
