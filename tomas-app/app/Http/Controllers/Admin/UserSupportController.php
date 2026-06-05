<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\UserSupportChat;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserSupportController extends Controller
{
    public function index(Request $request)
    {
        $selectedUserId = $request->query('user_id');

        $threads = UserSupportChat::with('user')
            ->orderByDesc('id_user_support_chat')
            ->get()
            ->groupBy('id_user')
            ->map(function ($messages) {
                $last = $messages->first();
                return [
                    'id_user'        => $last->id_user,
                    'user'           => $last->user,
                    'kategori'       => $last->kategori,
                    'last_message'   => $last->pesan,
                    'last_time'      => $last->created_at,
                    'total_messages' => $messages->count(),
                    'from_user'     => $messages->where('dari_user', true)->count(),
                    'from_admin'    => $messages->where('dari_user', false)->count(),
                ];
            })
            ->sortByDesc(fn ($thread) => optional($thread['last_time'])->getTimestamp() ?? 0)
            ->values();

        if (!$selectedUserId && $threads->isNotEmpty()) {
            $selectedUserId = (string) $threads->first()['id_user'];
        }

        $selectedUser = $selectedUserId ? User::find($selectedUserId) : null;
        $messages = $selectedUserId
            ? UserSupportChat::where('id_user', $selectedUserId)
                ->orderBy('id_user_support_chat')
                ->get()
            : collect();

        return view('admin.support_user.index', compact(
            'threads',
            'selectedUser',
            'selectedUserId',
            'messages'
        ));
    }

    public function reply(Request $request, $id_user)
    {
        $request->validate([
            'pesan' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($id_user);
        $kategori = UserSupportChat::where('id_user', $id_user)
            ->orderByDesc('id_user_support_chat')
            ->value('kategori') ?? 'bantuan';

        UserSupportChat::create([
            'id_user'    => $user->id_user,
            'kategori'   => $kategori,
            'pesan'      => $request->pesan,
            'dari_user'  => false,
        ]);

        Notifikasi::kirim(
            $user->id_user,
            'Balasan dari Pusat Bantuan',
            $request->pesan,
            'support'
        );

        $tokens = FcmToken::getTokens('user', $user->id_user);
        FcmService::sendToMany(
            $tokens,
            'Balasan dari Pusat Bantuan',
            Str::limit($request->pesan, 80),
            ['type' => 'support', 'id_user' => (string) $user->id_user]
        );

        return back()->with('success', 'Balasan berhasil dikirim ke user.');
    }
}
