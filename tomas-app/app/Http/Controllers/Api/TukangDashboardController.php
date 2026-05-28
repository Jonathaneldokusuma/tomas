<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Tukang;
use App\Models\Chat;
use App\Models\User;
use App\Models\BroadcastMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TukangDashboardController extends Controller
{
    // Helper: ambil tukang dari token header
    private function getTukang(Request $request)
    {
        $token = $request->header('X-Tukang-Token');
        if (!$token) return null;
        $decoded = base64_decode($token);
        $parts = explode('|', $decoded);
        if (count($parts) < 2 || $parts[1] !== 'tukang') return null;
        return Tukang::where('username', $parts[0])->first();
    }

    // GET /api/tukang/orders - semua order
    public function orders(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $orders = Order::with(['user', 'layanan'])
            ->where('id_tukang', $tukang->id_tukang)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['orders' => $orders]);
    }

    // GET /api/tukang/orders/pending
    public function pendingOrders(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $orders = Order::with(['user', 'layanan'])
            ->where('id_tukang', $tukang->id_tukang)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['orders' => $orders]);
    }

    // POST /api/tukang/orders/{id}/accept
    public function acceptOrder(Request $request, $id)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $order = Order::where('id_order', $id)
            ->where('id_tukang', $tukang->id_tukang)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);
        if ($order->status !== 'pending') return response()->json(['message' => 'Order sudah diproses'], 400);

        $order->update(['status' => 'confirmed']);

        return response()->json(['message' => 'Order diterima', 'order' => $order]);
    }

    // POST /api/tukang/orders/{id}/reject
    public function rejectOrder(Request $request, $id)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $order = Order::where('id_order', $id)
            ->where('id_tukang', $tukang->id_tukang)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);
        if ($order->status !== 'pending') return response()->json(['message' => 'Order sudah diproses'], 400);

        $order->update([
            'status' => 'rejected',
            'catatan_tukang' => $request->input('catatan', ''),
        ]);

        return response()->json(['message' => 'Order ditolak', 'order' => $order]);
    }

    // POST /api/tukang/orders/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_progress,done',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $order = Order::where('id_order', $id)
            ->where('id_tukang', $tukang->id_tukang)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);

        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Status diperbarui', 'order' => $order]);
    }

    // POST /api/tukang/orders/{id}/confirm-payment
    public function confirmPayment(Request $request, $id)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $order = Order::where('id_order', $id)
            ->where('id_tukang', $tukang->id_tukang)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);
        if ($order->status_payment !== 'uploaded') {
            return response()->json(['message' => 'Belum ada bukti bayar dari user'], 400);
        }

        $order->update(['status_payment' => 'confirmed', 'status' => 'done']);

        return response()->json(['message' => 'Pembayaran dikonfirmasi', 'order' => $order]);
    }

    // GET /api/tukang/profile
    public function profile(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        return response()->json(['tukang' => $tukang]);
    }

    // PUT /api/tukang/profile
    public function updateProfile(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'nama'        => 'sometimes|string|max:100',
            'bio'         => 'sometimes|string',
            'no_hp'       => 'sometimes|string|max:20',
            'lokasi'      => 'sometimes|string|max:200',
            'kategori'    => 'sometimes|string|max:100',
            'tarif'       => 'sometimes|numeric|min:0',
            'password'    => 'sometimes|string|min:6',
            'foto'        => 'sometimes|image|max:2048',
            'status_aktif' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $data = $request->only(['nama', 'bio', 'no_hp', 'lokasi', 'kategori', 'tarif', 'status_aktif']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            if ($tukang->foto) Storage::disk('public')->delete($tukang->foto);
            $data['foto'] = $request->file('foto')->store('tukang/foto', 'public');
        }

        $tukang->update($data);

        return response()->json(['message' => 'Profil diperbarui', 'tukang' => $tukang->fresh()]);
    }

    // POST /api/tukang/upload-ktp
    public function uploadKtp(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'foto_ktp'    => 'required|image|max:4096',
            'foto_selfie' => 'required|image|max:4096',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        if ($tukang->foto_ktp) Storage::disk('public')->delete($tukang->foto_ktp);
        if ($tukang->foto_selfie) Storage::disk('public')->delete($tukang->foto_selfie);

        $tukang->update([
            'foto_ktp'          => $request->file('foto_ktp')->store('tukang/ktp', 'public'),
            'foto_selfie'       => $request->file('foto_selfie')->store('tukang/selfie', 'public'),
            'status_verifikasi' => 'pending',
        ]);

        return response()->json(['message' => 'Dokumen berhasil dikirim, menunggu verifikasi.']);
    }

    // ── CHAT ─────────────────────────────────────────────────────

    // GET /api/tukang/chat - daftar percakapan (per user)
    public function chatInbox(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $userIds = Chat::where('id_tukang', $tukang->id_tukang)
            ->selectRaw('id_user, MAX(id_chat) as last_id')
            ->groupBy('id_user')
            ->orderByDesc('last_id')
            ->pluck('id_user');

        $result = $userIds->map(function ($uid) use ($tukang) {
            $user = User::find($uid);
            if (!$user) return null;
            $last = Chat::where('id_tukang', $tukang->id_tukang)
                ->where('id_user', $uid)
                ->latest('id_chat')->first();
            $unread = Chat::where('id_tukang', $tukang->id_tukang)
                ->where('id_user', $uid)
                ->where('dari_user', 1)
                ->count();
            return [
                'user' => ['id_user' => $user->id_user, 'name' => $user->name, 'no_hp' => $user->no_hp ?? ''],
                'last_message' => $last ? $last->pesan : '',
                'last_time'    => $last ? $last->created_at : null,
                'unread'       => $unread,
            ];
        })->filter()->values();

        return response()->json(['conversations' => $result]);
    }

    // GET /api/tukang/chat/{id_user} - percakapan dengan 1 user
    public function chatMessages(Request $request, $id_user)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $user = User::find($id_user);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $messages = Chat::where('id_tukang', $tukang->id_tukang)
            ->where('id_user', $id_user)
            ->orderBy('id_chat')
            ->get(['id_chat', 'pesan', 'dari_user', 'created_at']);

        return response()->json([
            'user'     => ['id_user' => $user->id_user, 'name' => $user->name],
            'messages' => $messages,
        ]);
    }

    // POST /api/tukang/chat/{id_user} - kirim pesan ke user
    public function chatSend(Request $request, $id_user)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate(['pesan' => 'required|string|max:1000']);

        $chat = Chat::create([
            'id_user'    => $id_user,
            'id_tukang'  => $tukang->id_tukang,
            'pesan'      => $request->pesan,
            'dari_user'  => false, // 0 = dari tukang
            'created_at' => now(),
        ]);

        return response()->json(['message' => $chat]);
    }

    // ── BROADCAST (Pesan dari Pusat) ──────────────────────────────

    // GET /api/tukang/broadcast
    public function broadcast(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $messages = BroadcastMessage::orderByDesc('created_at')->take(50)->get();
        return response()->json(['broadcasts' => $messages]);
    }
}
