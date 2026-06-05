<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSupportChat;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    private function categories(): array
    {
        return [
            'Bantuan',
            'Bug Aplikasi',
            'Akun',
            'Pembayaran',
            'Pesanan',
            'Lainnya',
        ];
    }

    public function index(Request $request)
    {
        $messages = UserSupportChat::where('id_user', $request->user()->id_user)
            ->orderBy('id_user_support_chat')
            ->get(['id_user_support_chat', 'kategori', 'pesan', 'dari_user', 'created_at']);

        return response()->json([
            'support' => $messages,
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $categories = $this->categories();

        $request->validate([
            'kategori' => 'required|string|in:' . implode(',', $categories),
            'pesan'    => 'required|string|max:1000',
        ]);

        $message = UserSupportChat::create([
            'id_user'    => $request->user()->id_user,
            'kategori'   => $request->kategori,
            'pesan'      => $request->pesan,
            'dari_user'  => true,
        ]);

        return response()->json([
            'message' => 'Pesan support terkirim.',
            'support' => $message->fresh(),
        ], 201);
    }
}
