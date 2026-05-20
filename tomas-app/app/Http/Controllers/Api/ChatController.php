<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Tukang;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id_user;

        $tukangIds = Chat::where('id_user', $userId)
            ->selectRaw('id_tukang, MAX(id_chat) as last_id')
            ->groupBy('id_tukang')
            ->orderByDesc('last_id')
            ->pluck('id_tukang');

        $result = $tukangIds->map(function ($tid) use ($userId) {
            $tukang = Tukang::find($tid);
            if (!$tukang) return null;

            $last   = Chat::where('id_user', $userId)->where('id_tukang', $tid)->latest('id_chat')->first();
            $unread = 0;

            return [
                'tukang' => [
                    'id_tukang'    => $tukang->id_tukang,
                    'nama'         => $tukang->nama,
                    'kategori'     => $tukang->kategori,
                    'status_aktif' => (bool) $tukang->status_aktif,
                    'foto_url'     => $tukang->foto ? url('storage/' . $tukang->foto) : null,
                ],
                'last_message' => $last ? $last->pesan : '',
                'unread'       => $unread,
            ];
        })->filter()->values();

        return response()->json($result);
    }

    public function show(Request $request, $id_tukang)
    {
        $userId = $request->user()->id_user;
        $tukang = Tukang::findOrFail($id_tukang);

        $messages = Chat::where('id_user', $userId)
            ->where('id_tukang', $id_tukang)
            ->orderBy('id_chat')
            ->get(['id_chat', 'pesan', 'dari_user', 'created_at']);

        // messages sent
        Chat::where('id_user', $userId)->where('id_tukang', $id_tukang)
            ->where('dari_user', false)->count(); // placeholder for future read tracking

        return response()->json([
            'tukang' => [
                'id_tukang'    => $tukang->id_tukang,
                'nama'         => $tukang->nama,
                'kategori'     => $tukang->kategori,
                'status_aktif' => (bool) $tukang->status_aktif,
                'foto_url'     => $tukang->foto ? url('storage/' . $tukang->foto) : null,
            ],
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, $id_tukang)
    {
        $request->validate(['pesan' => 'required|string|max:1000']);

        $chat = Chat::create([
            'id_user'    => $request->user()->id_user,
            'id_tukang'  => $id_tukang,
            'pesan'      => $request->pesan,
            'dari_user'  => true,
            'created_at' => now(),
        ]);

        return response()->json($chat, 201);
    }
}
