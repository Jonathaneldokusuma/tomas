<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pembayaran;
use App\Models\Tukang;
use App\Models\Notifikasi;
use App\Models\FcmToken;
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
            'bukti_bayar_url'=> $o->bukti_bayar ? url('storage/' . $o->bukti_bayar) : null,
            'created_at'     => $o->created_at?->toDateTimeString(),
            'has_review'     => (bool) $o->review,
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
        ]);

        $tukang = Tukang::find($request->id_tukang);
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

    // GET /api/orders/{id}
    public function show(Request $request, $id)
    {
        $order = Order::with(['tukang', 'layanan', 'review'])
            ->where('id_order', $id)
            ->where('id_user', $request->user()->id_user)
            ->first();

        if (!$order) return response()->json(['message' => 'Order tidak ditemukan'], 404);

        return response()->json([
            'id_order'       => $order->id_order,
            'tukang'         => $order->tukang,
            'layanan'        => $order->layanan,
            'alamat'         => $order->alamat,
            'latitude'       => $order->latitude,
            'longitude'      => $order->longitude,
            'tanggal_kerja'  => $order->tanggal_kerja,
            'jam_mulai'      => $order->jam_mulai,
            'durasi'         => $order->durasi,
            'deskripsi'      => $order->deskripsi,
            'metode_bayar'   => $order->metode_bayar,
            'status'         => $order->status,
            'status_payment' => $order->status_payment,
            'bukti_bayar_url'=> $order->bukti_bayar ? url('storage/' . $order->bukti_bayar) : null,
            'catatan_tukang' => $order->catatan_tukang,
            'created_at'     => $order->created_at?->toDateTimeString(),
            'has_review'     => (bool) $order->review,
        ]);
    }
}
