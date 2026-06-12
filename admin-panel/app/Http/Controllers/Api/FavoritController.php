<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorit;
use Illuminate\Http\Request;

class FavoritController extends Controller
{
    public function index(Request $request)
    {
        $favs = Favorit::with('tukang')
            ->where('id_user', $request->user()->id_user)
            ->latest('id_favorit')
            ->get();

        return response()->json($favs->map(fn($f) => [
            'id_favorit' => $f->id_favorit,
            'tukang' => $f->tukang ? [
                'id_tukang'    => $f->tukang->id_tukang,
                'nama'         => $f->tukang->nama,
                'kategori'     => $f->tukang->kategori,
                'lokasi'       => $f->tukang->lokasi,
                'status_aktif' => (bool) $f->tukang->status_aktif,
                'foto_url'     => $f->tukang->foto ? url('storage/' . $f->tukang->foto) : null,
            ] : null,
        ]));
    }

    public function toggle(Request $request, $id_tukang)
    {
        $userId  = $request->user()->id_user;
        $existing = Favorit::where('id_user', $userId)->where('id_tukang', $id_tukang)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        Favorit::create(['id_user' => $userId, 'id_tukang' => $id_tukang, 'created_at' => now()]);
        return response()->json(['favorited' => true]);
    }

    public function check(Request $request, $id_tukang)
    {
        $favorited = Favorit::where('id_user', $request->user()->id_user)
            ->where('id_tukang', $id_tukang)
            ->exists();
        return response()->json(['favorited' => $favorited]);
    }
}
