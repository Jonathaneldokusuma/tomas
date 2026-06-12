<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Order;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Konfirmasi pembayaran manual (masukkan nomor referensi → langsung paid).
     */
    public function pay(Request $request, $id_order)
    {
        $user  = $request->user();
        $order = Order::with(['tukang'])
            ->where('id_order', $id_order)
            ->where('id_user', $user->id_user)
            ->firstOrFail();

        // Sudah dibayar?
        $existing = Pembayaran::where('id_order', $id_order)
            ->where('status', 'paid')
            ->first();
        if ($existing) {
            return response()->json(['message' => 'Order sudah dibayar.'], 409);
        }

        $request->validate([
            'nomor_referensi' => 'required|string|max:100',
        ]);

        $jumlah = $order->tukang->tarif ?? 0;

        Pembayaran::updateOrCreate(
            ['id_order' => $id_order],
            [
                'jumlah'         => $jumlah,
                'status'         => 'paid',
                'payment_type'   => 'manual',
                'transaction_id' => $request->nomor_referensi,
            ]
        );

        Notifikasi::kirim(
            $user->id_user,
            'Pembayaran Dikonfirmasi',
            'Pembayaran pesanan Anda sebesar Rp' . number_format($jumlah, 0, ',', '.') . ' telah dikonfirmasi.',
            'order'
        );

        return response()->json(['message' => 'Pembayaran berhasil dikonfirmasi.', 'status' => 'paid']);
    }

    /**
     * Get payment status for an order.
     */
    public function status(Request $request, $id_order)
    {
        $user  = $request->user();
        $order = Order::where('id_order', $id_order)
            ->where('id_user', $user->id_user)
            ->firstOrFail();

        $pembayaran = Pembayaran::where('id_order', $id_order)
            ->orderByDesc('id_pembayaran')
            ->first();

        if (!$pembayaran) {
            return response()->json(['status' => 'unpaid', 'snap_url' => null]);
        }

        return response()->json([
            'status'     => $pembayaran->status,
            'snap_url'   => $pembayaran->snap_url,
            'snap_token' => $pembayaran->snap_token,
            'jumlah'     => $pembayaran->jumlah,
        ]);
    }
}
