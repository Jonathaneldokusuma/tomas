<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pembayaran;
use App\Models\Tukang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

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
            'id_order'      => $o->id_order,
            'tukang'        => $o->tukang ? [
                'id_tukang' => $o->tukang->id_tukang,
                'nama'      => $o->tukang->nama,
                'kategori'  => $o->tukang->kategori,
                'lokasi'    => $o->tukang->lokasi,
                'tarif'     => $o->tukang->tarif,
                'foto_url'  => $o->tukang->foto ? url('storage/' . $o->tukang->foto) : null,
            ] : null,
            'layanan'       => $o->layanan ? ['id_layanan' => $o->layanan->id_layanan, 'nama_layanan' => $o->layanan->nama_layanan] : null,
            'alamat'        => $o->alamat,
            'tanggal_kerja' => $o->tanggal_kerja,
            'jam_mulai'     => $o->jam_mulai,
            'durasi'        => $o->durasi,
            'deskripsi'     => $o->deskripsi,
            'metode_bayar'  => $o->metode_bayar,
            'status'        => $o->status ?? 'pending',
            'created_at'    => $o->created_at?->toDateTimeString(),
            'has_review'    => (bool) $o->review,
            'pembayaran'    => Pembayaran::where('id_order', $o->id_order)->orderByDesc('id_pembayaran')->first()
                ? ['status' => Pembayaran::where('id_order', $o->id_order)->orderByDesc('id_pembayaran')->first()->status]
                : ['status' => 'unpaid'],
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tukang'    => 'required|integer|exists:tukang,id_tukang',
            'id_layanan'   => 'required|integer|exists:layanan,id_layanan',
            'alamat'       => 'nullable|string|max:255',
            'tanggal_kerja'=> 'nullable|date',
            'jam_mulai'    => 'nullable|string|max:10',
            'durasi'       => 'nullable|string|max:50',
            'deskripsi'    => 'nullable|string',
            'metode_bayar' => 'nullable|string|max:50',
        ]);

        $order = Order::create([
            'id_user'       => $request->user()->id_user,
            'id_tukang'     => $request->id_tukang,
            'id_layanan'    => $request->id_layanan,
            'alamat'        => $request->alamat,
            'tanggal_kerja' => $request->tanggal_kerja,
            'jam_mulai'     => $request->jam_mulai,
            'durasi'        => $request->durasi,
            'deskripsi'     => $request->deskripsi,
            'metode_bayar'  => $request->metode_bayar ?? 'Tunai',
            'status'        => 'pending',
        ]);

        $tukang = Tukang::find($request->id_tukang);
        Notifikasi::kirim(
            $request->user()->id_user,
            'Pesanan Dibuat',
            'Pesanan Anda ke ' . ($tukang->nama ?? 'tukang') . ' berhasil dibuat.',
            'order'
        );

        return response()->json(['id_order' => $order->id_order, 'message' => 'Order berhasil.'], 201);
    }
}
