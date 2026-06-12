<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $notifs = Notifikasi::where('id_user', $userId)
            ->orderByDesc('created_at')
            ->get();

        // Tandai semua sudah dibaca
        Notifikasi::where('id_user', $userId)->where('dibaca', 0)->update(['dibaca' => 1]);

        return view('notifikasi.index', compact('notifs'));
    }

    /** Jumlah notif belum dibaca (untuk badge) — JSON */
    public function unread()
    {
        $userId = session('user_id');
        $count  = $userId
            ? Notifikasi::where('id_user', $userId)->where('dibaca', 0)->count()
            : 0;

        return response()->json(['count' => $count]);
    }
}
