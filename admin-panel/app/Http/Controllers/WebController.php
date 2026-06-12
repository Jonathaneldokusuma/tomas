<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Tukang;
use App\Models\Order;
use App\Models\Review;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function home()
    {
        $layanan    = Layanan::all();
        $tukangList = Tukang::where('status_aktif', 1)->get();
        return view('home', compact('layanan', 'tukangList'));
    }

    // chat() and chatWith() removed — now handled by ChatController

    public function profile()
    {
        return view('profile');
    }

    public function tambah()
    {
        $layanan    = Layanan::all();
        $allLayanan = $layanan;
        $tukangList = Tukang::all();
        return view('tukang.index', compact('tukangList', 'layanan', 'allLayanan'));
    }

    public function tukangIndex(Request $request)
    {
        $layanan     = Layanan::all();
        $allLayanan  = $layanan;
        $aktifFilter = $request->query('kategori');

        $query = Tukang::query();
        if ($aktifFilter) {
            $query->where('kategori', $aktifFilter);
        }
        $tukangList = $query->get();

        return view('tukang.index', compact('tukangList', 'layanan', 'allLayanan', 'aktifFilter'));
    }

    public function tukangShow($id)
    {
        $tukang = Tukang::findOrFail($id);
        return view('tukang.show', compact('tukang'));
    }

    public function layananShow($id)
    {
        $layanan    = Layanan::findOrFail($id);
        $allLayanan = Layanan::all();
        $tukangList = Tukang::where('kategori', $layanan->nama_layanan)->get();
        $aktifFilter = $layanan->nama_layanan;
        return view('tukang.index', compact('tukangList', 'layanan', 'allLayanan', 'aktifFilter'));
    }

    public function orderStore(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $request->validate([
            'id_tukang'  => 'required|integer',
            'id_layanan' => 'required|integer',
        ]);

        Order::create([
            'id_user'    => $userId,
            'id_tukang'  => $request->id_tukang,
            'id_layanan' => $request->id_layanan,
        ]);

        // Notif konfirmasi order
        $tukang  = Tukang::find($request->id_tukang);
        $layanan = Layanan::find($request->id_layanan);
        Notifikasi::kirim(
            $userId,
            'Pesanan Berhasil Dibuat',
            'Pesanan Anda untuk ' . ($tukang->nama ?? 'tukang') . ' (' . ($layanan->nama_layanan ?? '') . ') telah diterima.',
            'order'
        );

        return redirect()->route('riwayat')->with('success', 'Pesanan berhasil dibuat!');
    }

    public function orderRestore($id_order)
    {
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');

        $original = Order::where('id_order', $id_order)
            ->where('id_user', $userId)
            ->firstOrFail();

        Order::create([
            'id_user'    => $userId,
            'id_tukang'  => $original->id_tukang,
            'id_layanan' => $original->id_layanan,
        ]);

        $tukang = Tukang::find($original->id_tukang);
        Notifikasi::kirim(
            $userId,
            'Sewa Ulang Berhasil',
            'Anda berhasil menyewa ulang ' . ($tukang->nama ?? 'tukang') . '.',
            'order'
        );

        return redirect()->route('riwayat')->with('success', 'Pesanan ulang berhasil!');
    }

    public function riwayat()
    {
        $userId = session('user_id');
        $orders = $userId
            ? Order::with(['tukang', 'layanan', 'review'])->where('id_user', $userId)->orderByDesc('id_order')->get()
            : collect();
        return view('riwayat', compact('orders'));
    }
}
