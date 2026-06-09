<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Portfolio;
use App\Models\BadgeAward;
use App\Models\Review;
use App\Models\SupportChat;
use App\Models\Layanan;
use App\Models\Tukang;
use App\Models\Chat;
use App\Models\User;
use App\Models\BroadcastMessage;
use App\Models\FcmToken;
use App\Models\Notifikasi;
use App\Services\FcmService;
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

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim(
            $order->id_user,
            'Pesanan Dikonfirmasi ✅',
            'Tukang ' . $tukang->nama . ' telah menerima pesanan Anda.',
            'order'
        );

        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $order->id_user);
        FcmService::sendToMany($tokens,
            'Pesanan Dikonfirmasi ✅',
            'Tukang ' . $tukang->nama . ' telah menerima pesanan Anda.',
            ['type' => 'order_confirmed', 'id_order' => (string) $id]
        );

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
            'status'         => 'rejected',
            'catatan_tukang' => $request->input('catatan', ''),
        ]);

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim(
            $order->id_user,
            'Pesanan Ditolak',
            'Maaf, tukang ' . $tukang->nama . ' tidak dapat menerima pesanan Anda saat ini.',
            'order'
        );

        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $order->id_user);
        FcmService::sendToMany($tokens,
            'Pesanan Ditolak',
            'Maaf, tukang tidak dapat menerima pesanan Anda saat ini.',
            ['type' => 'order_rejected', 'id_order' => (string) $id]
        );

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

        $statusMsg   = $request->status === 'in_progress'
            ? 'Tukang ' . $tukang->nama . ' sedang mengerjakan pesanan Anda.'
            : 'Tukang ' . $tukang->nama . ' telah menyelesaikan pekerjaan.';
        $statusTitle = $request->status === 'in_progress' ? 'Pekerjaan Dimulai 🔧' : 'Pekerjaan Selesai ✅';

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim($order->id_user, $statusTitle, $statusMsg, 'order');

        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $order->id_user);
        FcmService::sendToMany($tokens, $statusTitle, $statusMsg,
            ['type' => 'order_status', 'status' => $request->status, 'id_order' => (string) $id]
        );

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

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim(
            $order->id_user,
            'Pembayaran Dikonfirmasi ✅',
            'Pembayaran Anda telah dikonfirmasi oleh tukang. Terima kasih!',
            'order'
        );

        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $order->id_user);
        FcmService::sendToMany($tokens,
            'Pembayaran Dikonfirmasi ✅',
            'Pembayaran Anda telah dikonfirmasi oleh tukang. Terima kasih!',
            ['type' => 'payment_confirmed', 'id_order' => (string) $id]
        );

        return response()->json(['message' => 'Pembayaran dikonfirmasi', 'order' => $order]);
    }

    // GET /api/tukang/profile
    public function profile(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        return response()->json($this->profilePayload($tukang));
    }

    // PUT /api/tukang/profile
    public function updateProfile(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'nama'        => 'sometimes|string|max:100',
            'bio'         => 'sometimes|string',
            'no_hp'       => 'sometimes|digits_between:8,15',
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

    // GET /api/tukang/portfolio
    public function portfolioIndex(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        return response()->json([
            'portfolio' => $this->portfolioList($tukang),
        ]);
    }

    // POST /api/tukang/portfolio
    public function portfolioStore(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'judul' => 'nullable|string|max:150',
            'deskripsi' => 'nullable|string|max:1000',
            'media' => 'required|image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $media = $request->file('media');
        $path = $media->store('tukang/portfolio', 'public');
        $item = Portfolio::create([
            'id_tukang' => $tukang->id_tukang,
            'judul' => $request->input('judul'),
            'deskripsi' => $request->input('deskripsi'),
            'media_path' => $path,
            'media_type' => 'image',
        ]);

        return response()->json([
            'message' => 'Portofolio ditambahkan',
            'portfolio' => $this->formatPortfolio($item->fresh()),
        ], 201);
    }

    // DELETE /api/tukang/portfolio/{id}
    public function portfolioDestroy(Request $request, $id)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $item = Portfolio::where('id_portfolio', $id)
            ->where('id_tukang', $tukang->id_tukang)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Portofolio tidak ditemukan'], 404);
        }

        if ($item->media_path) {
            Storage::disk('public')->delete($item->media_path);
        }

        $item->delete();

        return response()->json(['message' => 'Portofolio dihapus']);
    }

    // GET /api/tukang/support
    public function supportIndex(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $messages = SupportChat::where('id_tukang', $tukang->id_tukang)
            ->orderBy('id_support_chat')
            ->get(['id_support_chat', 'kategori', 'pesan', 'dari_tukang', 'created_at']);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    // POST /api/tukang/support
    public function supportStore(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $validator = Validator::make($request->all(), [
            'kategori' => 'required|in:bantuan,laporan,bug,saran,lainnya',
            'pesan'    => 'required|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = SupportChat::create([
            'id_tukang'   => $tukang->id_tukang,
            'kategori'    => $request->input('kategori', 'bantuan'),
            'pesan'       => $request->input('pesan'),
            'dari_tukang' => true,
        ]);

        return response()->json([
            'message' => 'Pesan terkirim ke pusat',
            'support' => $message->fresh(),
        ], 201);
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

        if ($tukang->foto_ktp)    Storage::disk('public')->delete($tukang->foto_ktp);
        if ($tukang->foto_selfie) Storage::disk('public')->delete($tukang->foto_selfie);

        $tukang->update([
            'foto_ktp'          => $request->file('foto_ktp')->store('tukang/ktp', 'public'),
            'foto_selfie'       => $request->file('foto_selfie')->store('tukang/selfie', 'public'),
            'status_verifikasi' => 'pending',
        ]);

        return response()->json(['message' => 'Dokumen berhasil dikirim, menunggu verifikasi.']);
    }

    // ── CHAT ─────────────────────────────────────────────────────

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
                ->where('id_user', $uid)->latest('id_chat')->first();
            $unread = Chat::where('id_tukang', $tukang->id_tukang)
                ->where('id_user', $uid)->where('dari_user', 1)->count();
            return [
                'user'         => ['id_user' => $user->id_user, 'name' => $user->name, 'no_hp' => $user->no_hp ?? ''],
                'last_message' => $last ? $last->pesan : '',
                'last_time'    => $last ? $last->created_at : null,
                'unread'       => $unread,
            ];
        })->filter()->values();

        return response()->json(['conversations' => $result]);
    }

    public function chatMessages(Request $request, $id_user)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $user = User::find($id_user);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $messages = Chat::where('id_tukang', $tukang->id_tukang)
            ->where('id_user', $id_user)->orderBy('id_chat')
            ->get(['id_chat', 'pesan', 'dari_user', 'created_at']);

        return response()->json([
            'user'     => ['id_user' => $user->id_user, 'name' => $user->name],
            'messages' => $messages,
        ]);
    }

    public function chatSend(Request $request, $id_user)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate(['pesan' => 'required|string|max:1000']);

        $chat = Chat::create([
            'id_user'    => $id_user,
            'id_tukang'  => $tukang->id_tukang,
            'pesan'      => $request->pesan,
            'dari_user'  => false,
            'created_at' => now(),
        ]);

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim(
            $id_user,
            'Pesan dari ' . $tukang->nama,
            \Str::limit($request->pesan, 80),
            'chat'
        );

        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $id_user);
        FcmService::sendToMany($tokens,
            'Pesan dari ' . $tukang->nama,
            \Str::limit($request->pesan, 80),
            ['type' => 'chat', 'id_tukang' => (string) $tukang->id_tukang]
        );

        return response()->json(['message' => $chat]);
    }

    // ── BROADCAST (Pesan dari Pusat) ──────────────────────────────

    public function broadcast(Request $request)
    {
        $tukang = $this->getTukang($request);
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        $messages = BroadcastMessage::orderByDesc('created_at')->take(50)->get();
        return response()->json(['broadcasts' => $messages]);
    }

    private function profilePayload(Tukang $tukang): array
    {
        $ordersQuery = Order::where('id_tukang', $tukang->id_tukang);
        $totalOrders = (clone $ordersQuery)->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'done')->count();
        $portfolioItems = Portfolio::where('id_tukang', $tukang->id_tukang)
            ->orderByDesc('id_portfolio')
            ->get();
        $reviews = Review::whereHas('order', function ($query) use ($tukang) {
            $query->where('id_tukang', $tukang->id_tukang);
        })->get(['rating']);
        $averageRating = $reviews->count() ? round((float) $reviews->avg('rating'), 1) : 0;
        $rank = $this->rankTukang($tukang);

        return [
            'tukang' => $tukang,
            'stats' => [
                'orders_total' => $totalOrders,
                'orders_done' => $completedOrders,
                'portfolio_count' => $portfolioItems->count(),
                'reviews_count' => $reviews->count(),
                'avg_rating' => $averageRating,
                'rank' => $rank,
            ],
            'badges' => array_values(array_merge(
                $this->buildBadges($tukang, $completedOrders, $reviews->count(), $averageRating, $portfolioItems->count(), $rank),
                $this->customBadges('tukang', $tukang->id_tukang)
            )),
            'portfolio' => $portfolioItems->map(fn ($item) => $this->formatPortfolio($item))->values(),
            'categories' => $this->categoryOptions(),
        ];
    }

    private function customBadges(string $targetType, int $targetId): array
    {
        return BadgeAward::forTarget($targetType, $targetId)
            ->map(fn (BadgeAward $badge) => $badge->toPayload())
            ->values()
            ->all();
    }

    private function categoryOptions()
    {
        $categories = Layanan::orderBy('nama_layanan')
            ->pluck('nama_layanan')
            ->filter()
            ->values();

        if ($categories->isEmpty()) {
            return collect($this->defaultCategories());
        }

        return $categories;
    }

    private function defaultCategories(): array
    {
        return [
            'Servis AC',
            'Instalasi Listrik',
            'Perbaikan Pipa & Plumbing',
            'Pengecatan Rumah',
            'Perbaikan Atap',
            'Bersih-Bersih Rumah',
            'Perbaikan Pintu & Jendela',
            'Taman & Lanskap',
        ];
    }

    private function portfolioList(Tukang $tukang)
    {
        return Portfolio::where('id_tukang', $tukang->id_tukang)
            ->orderByDesc('id_portfolio')
            ->get()
            ->map(fn ($item) => $this->formatPortfolio($item))
            ->values();
    }

    private function formatPortfolio(Portfolio $item): array
    {
        return [
            'id_portfolio' => $item->id_portfolio,
            'judul' => $item->judul ?? 'Portofolio',
            'deskripsi' => $item->deskripsi ?? '',
            'media_url' => $item->media_path ? url('storage/' . $item->media_path) : null,
            'media_type' => $item->media_type ?? 'image',
            'created_at' => optional($item->created_at)->toISOString(),
        ];
    }

    private function buildBadges(
        Tukang $tukang,
        int $completedOrders,
        int $reviewsCount,
        float $avgRating,
        int $portfolioCount,
        int $rank
    ): array {
        $badges = [];

        if ($tukang->status_verifikasi === 'verified') {
            $badges[] = [
                'key' => 'verified_partner',
                'label' => 'Verified Partner',
                'description' => 'Sudah diverifikasi admin',
                'icon' => 'verified',
                'color' => '#16A34A',
            ];
        }

        if ($completedOrders >= 1) {
            $badges[] = [
                'key' => 'first_job',
                'label' => 'Job Starter',
                'description' => 'Pernah menyelesaikan pekerjaan',
                'icon' => 'work_history',
                'color' => '#2563EB',
            ];
        }

        if ($completedOrders >= 10) {
            $badges[] = [
                'key' => 'pro_worker',
                'label' => 'Pro Worker',
                'description' => 'Lebih dari 10 pekerjaan selesai',
                'icon' => 'workspace_premium',
                'color' => '#7C3AED',
            ];
        }

        if ($portfolioCount >= 3) {
            $badges[] = [
                'key' => 'portfolio_pro',
                'label' => 'Portfolio Pro',
                'description' => 'Punya portofolio yang lengkap',
                'icon' => 'collections',
                'color' => '#0EA5E9',
            ];
        }

        if ($reviewsCount >= 5 && $avgRating >= 4.8) {
            $badges[] = [
                'key' => 'top_rated',
                'label' => 'Top Rated',
                'description' => 'Nilai pelanggan sangat tinggi',
                'icon' => 'star',
                'color' => '#F59E0B',
            ];
        }

        if ($rank === 1) {
            $badges[] = [
                'key' => 'no_1_tukang',
                'label' => '#1 Tukang',
                'description' => 'Peringkat terbaik saat ini',
                'icon' => 'emoji_events',
                'color' => '#DC2626',
            ];
        }

        return $badges;
    }

    private function rankTukang(Tukang $current): int
    {
        $workers = Tukang::where('status_aktif', 1)
            ->where('status_verifikasi', 'verified')
            ->get();

        $scores = $workers->map(function (Tukang $worker) {
            $ordersQuery = Order::where('id_tukang', $worker->id_tukang);
            $completedOrders = (clone $ordersQuery)->where('status', 'done')->count();
            $reviews = Review::whereHas('order', function ($query) use ($worker) {
                $query->where('id_tukang', $worker->id_tukang);
            })->get(['rating']);
            $avgRating = $reviews->count() ? (float) $reviews->avg('rating') : 0;
            $portfolioCount = Portfolio::where('id_tukang', $worker->id_tukang)->count();

            return [
                'id' => $worker->id_tukang,
                'score' => ($completedOrders * 10) + ($avgRating * 6) + ($portfolioCount * 2),
            ];
        })->sortByDesc('score')->values();

        $position = $scores->search(fn ($row) => (int) $row['id'] === (int) $current->id_tukang);

        return $position === false ? 0 : $position + 1;
    }
}
