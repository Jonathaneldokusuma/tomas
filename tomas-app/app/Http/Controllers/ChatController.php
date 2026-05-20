<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Tukang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /** Daftar tukang yang pernah di-chat oleh user */
    public function index()
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        // Ambil percakapan terakhir per tukang
        $chatList = Chat::where('id_user', $userId)
            ->with('tukang')
            ->get()
            ->groupBy('id_tukang')
            ->map(function ($msgs) {
                $last = $msgs->sortByDesc('created_at')->first();
                return [
                    'tukang'  => $last->tukang,
                    'last'    => $last,
                    'unread'  => $msgs->where('dari_user', 0)->count(),
                ];
            })
            ->values();

        return view('chat.index', compact('chatList'));
    }

    /** Tampilkan percakapan dengan 1 tukang */
    public function show($id_tukang)
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $tukang   = Tukang::findOrFail($id_tukang);
        $messages = Chat::where('id_user', $userId)
            ->where('id_tukang', $id_tukang)
            ->orderBy('created_at')
            ->get();

        return view('chat.show', compact('tukang', 'messages'));
    }

    /** Kirim pesan */
    public function send(Request $request, $id_tukang)
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $request->validate(['pesan' => 'required|string|max:1000']);

        Chat::create([
            'id_user'    => $userId,
            'id_tukang'  => $id_tukang,
            'pesan'      => $request->pesan,
            'dari_user'  => 1,
            'created_at' => now(),
        ]);

        // Simulasi balasan otomatis dari tukang
        $tukang = Tukang::find($id_tukang);
        Chat::create([
            'id_user'    => $userId,
            'id_tukang'  => $id_tukang,
            'pesan'      => 'Halo! Terima kasih sudah menghubungi ' . ($tukang->nama ?? 'kami') . '. Kami akan segera membalas.',
            'dari_user'  => 0,
            'created_at' => now()->addSecond(),
        ]);

        // Notif balasan dari tukang
        Notifikasi::kirim(
            $userId,
            'Pesan Baru dari ' . ($tukang->nama ?? 'Tukang'),
            ($tukang->nama ?? 'Tukang') . ' membalas pesan Anda.',
            'chat'
        );

        return redirect()->route('chat.show', $id_tukang);
    }
}
