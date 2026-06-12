<?php

namespace App\Http\Controllers;

use App\Models\Favorit;
use App\Models\Tukang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class FavoritController extends Controller
{
    /** Toggle favorit (tambah / hapus) — JSON response untuk AJAX */
    public function toggle($id_tukang)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $tukang = Tukang::findOrFail($id_tukang);

        $existing = Favorit::where('id_user', $userId)
            ->where('id_tukang', $id_tukang)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            Favorit::create([
                'id_user'    => $userId,
                'id_tukang'  => $id_tukang,
                'created_at' => now(),
            ]);
            $favorited = true;

            // Notif saat difavoritkan
            Notifikasi::kirim(
                $userId,
                'Tukang Difavoritkan',
                $tukang->nama . ' berhasil ditambahkan ke daftar favorit Anda.',
                'favorit'
            );
        }

        return response()->json([
            'favorited' => $favorited,
            'count'     => Favorit::where('id_tukang', $id_tukang)->count(),
        ]);
    }

    /** Halaman daftar favorit */
    public function index()
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $favorits = Favorit::where('id_user', $userId)
            ->with('tukang')
            ->orderByDesc('created_at')
            ->get();

        return view('favorit.index', compact('favorits'));
    }
}
