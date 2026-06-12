<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pembayaran;
use App\Models\Tukang;
use App\Models\FcmToken;
use App\Models\Notifikasi;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $orders = Order::with(['tukang', 'layanan', 'review'])
            ->where('id_user', $user->id_user)
            ->orderByDesc('id_order')
            ->get();

        return response()->json($orders->map(fn($o) => [
            'id_order'       => $o->id_order,
            'tukang'         => $o->tukang ? [
                'id_tukang' => $o->tukang->id_tukang,
                'nama'      => $o->tukang->nama,
                'kategori'  => $o->tukang->kategori,
                'lokasi'    => $o->tukang->lokasi,
                'tarif'     => $o->tukang->tarif,
                'foto_url'  => $o->tukang->foto ? url('storage/' . $o->tukang->foto) : null,
            ] : null,
            'layanan'        => $o->layanan ? ['id_layanan' => $o->layanan->id_layanan, 'nama_layanan' => $o->layanan->nama_layanan] : null,
            'alamat'         => $o->alamat,
            'tanggal_kerja'  => $o->tanggal_kerja,
            'jam_mulai'      => $o->jam_mulai,
            'durasi'         => $o->durasi,
            'deskripsi'      => $o->deskripsi,
            'metode_bayar'   => $o->metode_bayar,
            'status'         => $o->status ?? 'pending',
            'status_payment' => $o->status_payment ?? 'pending',
            'difficulty_level' => $o->difficulty_level ?? 'medium',
            'deposit_fee'    => (float) ($o->deposit_fee ?? 0),
            'user_completed_at' => optional($o->user_completed_at)->toDateTimeString(),
            'tukang_completed_at' => optional($o->tukang_completed_at)->toDateTimeString(),
            'deposit_deducted_at' => optional($o->deposit_deducted_at)->toDateTimeString(),
            'bukti_bayar_url'=> $o->bukti_bayar ? url('storage/' . $o->bukti_bayar) : null,
            'created_at'     => $o->created_at?->toDateTimeString(),
            'has_review'     => (bool) $o->review,
            'completion_status' => $this->completionStatus($o),
            'pembayaran'     => Pembayaran::where('id_order', $o->id_order)->orderByDesc('id_pembayaran')->first()
                ? ['status' => Pembayaran::where('id_order', $o->id_order)->orderByDesc('id_pembayaran')->first()->status]
                : ['status' => 'unpaid'],
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tukang'     => 'required|integer|exists:tukang,id_tukang',
            'id_layanan'    => 'required|integer|exists:layanan,id_layanan',
            'alamat'        => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'tanggal_kerja' => 'nullable|date',
            'jam_mulai'     => 'nullable|string|max:10',
            'durasi'        => 'nullable|string|max:50',
            'deskripsi'     => 'nullable|string',
            'metode_bayar'  => 'nullable|string|max:50',
        ]);

        $tukang = Tukang::find($request->id_tukang);
        $order = Order::create([
            'id_user'        => $request->user()->id_user,
            'id_tukang'      => $request->id_tukang,
            'id_layanan'     => $request->id_layanan,
            'alamat'         => $request->alamat,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'tanggal_kerja'  => $request->tanggal_kerja,
            'jam_mulai'      => $request->jam_mulai,
            'durasi'         => $request->durasi,
            'deskripsi'      => $request->deskripsi,
            'metode_bayar'   => $request->metode_bayar ?? 'Tunai',
            'status'         => 'pending',
            'status_payment' => 'pending',
            'difficulty_level' => $this->resolveDifficultyLevel($request->all(), $tukang),
            'deposit_fee'    => $this->resolveDepositFee($request->all(), $tukang),
        ]);

        $user   = $request->user();

        // ── Notifikasi in-app ke user (konfirmasi order dibuat) ──
        Notifikasi::kirim(
            $user->id_user,
            'Pesanan Dibuat',
            'Pesanan Anda ke ' . ($tukang->nama ?? 'tukang') . ' berhasil dibuat. Menunggu konfirmasi tukang.',
            'order'
        );

        // ── FCM push ke tukang ──
        if ($tukang) {
            $tokens = FcmToken::getTokens('tukang', $tukang->id_tukang);
            FcmService::sendToMany($tokens,
                'Pesanan Baru! 🔔',
                'Ada pesanan baru dari ' . ($user->nama ?? 'pelanggan') . '.',
                ['type' => 'new_order', 'id_order' => (string) $order->id_order]
            );
        }

        return response()->json(['id_order' => $order->id_order, 'message' => 'Order berhasil.'], 201);
    }

    public function confirmCompletion(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::where('id_order', $id)
            ->where('id_user', $user->id_user)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        if (!in_array($order->status, ['in_progress', 'done'])) {
            return response()->json(['message' => 'Order belum dapat diselesaikan'], 400);
        }

        if (! $order->user_completed_at) {
            $order->user_completed_at = now();
        }

        $order->save();
        $this->maybeFinalizeDeposit($order);

        return response()->json([
            'message' => 'Konfirmasi selesai dari user diterima',
            'order' => $this->formatOrder($order->fresh(['tukang', 'layanan', 'review'])),
        ]);
    }

    // POST /api/orders/{id}/upload-bukti
    public function uploadBuktiBayar(Request $request, $id)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|max:4096',
        ]);

        $order = Order::where('id_order', $id)
            ->where('id_user', $request->user()->id_user)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);
        if (!in_array($order->status, ['confirmed', 'in_progress'])) {
            return response()->json(['message' => 'Order belum dikonfirmasi tukang'], 400);
        }

        if ($order->bukti_bayar) Storage::disk('public')->delete($order->bukti_bayar);

        $path = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
        $order->update(['bukti_bayar' => $path, 'status_payment' => 'uploaded']);

        // ── FCM push ke tukang agar cek bukti bayar ──
        if ($order->id_tukang) {
            $tukangTokens = FcmToken::getTokens('tukang', $order->id_tukang);
            FcmService::sendToMany($tukangTokens,
                'Bukti Bayar Dikirim 💰',
                'Pelanggan telah mengirim bukti pembayaran. Silakan cek dan konfirmasi.',
                ['type' => 'bukti_bayar', 'id_order' => (string) $order->id_order]
            );
        }

        return response()->json([
            'message'         => 'Bukti bayar berhasil diupload',
            'bukti_bayar_url' => url('storage/' . $path),
        ]);
    }

    private function formatOrder(Order $o): array
    {
        return [
            'id_order'       => $o->id_order,
            'tukang'         => $o->tukang ? [
                'id_tukang' => $o->tukang->id_tukang,
                'nama'      => $o->tukang->nama,
                'kategori'  => $o->tukang->kategori,
                'lokasi'    => $o->tukang->lokasi,
                'tarif'     => $o->tukang->tarif,
                'foto_url'  => $o->tukang->foto ? url('storage/' . $o->tukang->foto) : null,
            ] : null,
            'layanan'        => $o->layanan ? ['id_layanan' => $o->layanan->id_layanan, 'nama_layanan' => $o->layanan->nama_layanan] : null,
            'alamat'         => $o->alamat,
            'tanggal_kerja'  => $o->tanggal_kerja,
            'jam_mulai'      => $o->jam_mulai,
            'durasi'         => $o->durasi,
            'deskripsi'      => $o->deskripsi,
            'metode_bayar'   => $o->metode_bayar,
            'status'         => $o->status ?? 'pending',
            'status_payment' => $o->status_payment ?? 'pending',
            'difficulty_level' => $o->difficulty_level ?? 'medium',
            'deposit_fee'    => (float) ($o->deposit_fee ?? 0),
            'user_completed_at' => optional($o->user_completed_at)->toDateTimeString(),
            'tukang_completed_at' => optional($o->tukang_completed_at)->toDateTimeString(),
            'deposit_deducted_at' => optional($o->deposit_deducted_at)->toDateTimeString(),
            'completion_status' => $this->completionStatus($o),
            'bukti_bayar_url'=> $o->bukti_bayar ? url('storage/' . $o->bukti_bayar) : null,
            'created_at'     => $o->created_at?->toDateTimeString(),
            'has_review'     => (bool) $o->review,
        ];
    }

    // GET /api/orders/{id}
    public function show(Request $request, $id)
    {
        $order = Order::with(['tukang', 'layanan', 'review'])
            ->where('id_order', $id)
            ->where('id_user', $request->user()->id_user)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);

        return response()->json($this->formatOrder($order));
    }

    private function resolveDifficultyLevel(array $input, ?Tukang $tukang): string
    {
        $text = strtolower(implode(' ', array_filter([
            $input['durasi'] ?? '',
            $input['deskripsi'] ?? '',
            $tukang?->kategori ?? '',
        ])));

        if (preg_match('/besar|renov|instalasi|kompleks|darurat|rumit|berat/', $text)) {
            return 'hard';
        }

        if (preg_match('/cepat|ringan|sederhana|simple|bersih|antar/', $text)) {
            return 'easy';
        }

        return 'medium';
    }

    private function resolveDepositFee(array $input, ?Tukang $tukang): float
    {
        $level = $this->resolveDifficultyLevel($input, $tukang);
        return match ($level) {
            'easy' => 10000,
            'hard' => 50000,
            default => 25000,
        };
    }

    private function completionStatus(Order $order): string
    {
        $userDone = (bool) $order->user_completed_at;
        $tukangDone = (bool) $order->tukang_completed_at;

        return match (true) {
            $userDone && $tukangDone => 'both_completed',
            $userDone => 'waiting_tukang',
            $tukangDone => 'waiting_user',
            default => 'waiting_completion',
        };
    }

    private function maybeFinalizeDeposit(Order $order): void
    {
        if ($order->deposit_deducted_at || ! $order->user_completed_at || ! $order->tukang_completed_at) {
            return;
        }

        $order->refresh();
        $order->loadMissing('tukang');
        $tukang = $order->tukang;
        if (! $tukang) {
            return;
        }

        $fee = (float) ($order->deposit_fee ?? 0);
        if ($fee <= 0) {
            return;
        }

        $tukang->deposit_balance = max(0, (float) $tukang->deposit_balance - $fee);
        $tukang->save();

        $order->deposit_deducted_at = now();
        $order->status = 'done';
        $order->save();

        Notifikasi::kirim(
            $order->id_user,
            'Pesanan Selesai',
            'Pesanan Anda sudah diselesaikan oleh kedua pihak dan deposit tukang telah dipotong.',
            'order'
        );

        $tokens = FcmToken::getTokens('user', $order->id_user);
        FcmService::sendToMany(
            $tokens,
            'Pesanan Selesai',
            'Pesanan Anda sudah diselesaikan oleh kedua pihak dan deposit tukang telah dipotong.',
            ['type' => 'order_done', 'id_order' => (string) $order->id_order]
        );
    }
}
