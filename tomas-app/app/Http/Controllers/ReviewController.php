<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Form review */
    public function create($id_order)
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $order = Order::with(['tukang', 'layanan', 'review'])
            ->where('id_order', $id_order)
            ->where('id_user', $userId)
            ->firstOrFail();

        // Kalau sudah pernah review, langsung ke riwayat
        if ($order->review) {
            return redirect()->route('riwayat')->with('info', 'Anda sudah memberikan ulasan untuk order ini.');
        }

        return view('review.create', compact('order'));
    }

    /** Simpan review */
    public function store(Request $request, $id_order)
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);

        $order = Order::where('id_order', $id_order)
            ->where('id_user', $userId)
            ->firstOrFail();

        Review::updateOrCreate(
            ['id_order' => $order->id_order],
            ['rating' => $request->rating, 'komentar' => $request->komentar]
        );

        Notifikasi::kirim(
            $userId,
            'Ulasan Berhasil Dikirim',
            'Terima kasih! Ulasan ' . $request->rating . ' bintang Anda untuk ' . ($order->tukang->nama ?? 'tukang') . ' telah tersimpan.',
            'review'
        );

        return redirect()->route('riwayat')->with('success', 'Ulasan berhasil disimpan!');
    }
}
