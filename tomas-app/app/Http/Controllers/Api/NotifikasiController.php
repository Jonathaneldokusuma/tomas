<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifs = Notifikasi::where('id_user', $request->user()->id_user)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifs->map(fn($n) => [
            'id_notif'   => $n->id_notif,
            'judul'      => $n->judul,
            'pesan'      => $n->pesan,
            'tipe'       => $n->tipe,
            'dibaca'     => (bool) $n->dibaca,
            'created_at' => $n->created_at?->toISOString(),
        ]));
    }

    public function markRead(Request $request, $id)
    {
        Notifikasi::where('id_notif', $id)
            ->where('id_user', $request->user()->id_user)
            ->update(['dibaca' => 1]);

        return response()->json(['message' => 'OK']);
    }

    public function markAllRead(Request $request)
    {
        Notifikasi::where('id_user', $request->user()->id_user)
            ->where('dibaca', 0)
            ->update(['dibaca' => 1]);

        return response()->json(['message' => 'OK']);
    }

    public function unreadCount(Request $request)
    {
        $count = Notifikasi::where('id_user', $request->user()->id_user)
            ->where('dibaca', 0)
            ->count();

        return response()->json(['count' => $count]);
    }
}
