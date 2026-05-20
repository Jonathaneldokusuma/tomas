<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tukang;
use App\Models\Layanan;
use Illuminate\Http\Request;

class TukangController extends Controller
{
    public function index(Request $request)
    {
        $q       = $request->query('q');
        $layanan = $request->query('layanan');

        $query = Tukang::query();
        if ($q) {
            $query->where(fn($q2) => $q2->where('nama', 'like', "%$q%")
                ->orWhere('kategori', 'like', "%$q%")
                ->orWhere('lokasi',   'like', "%$q%"));
        }
        if ($layanan) $query->where('kategori', $layanan);

        return response()->json($query->latest()->get()->map(fn($t) => $this->fmt($t)));
    }

    public function show($id)
    {
        return response()->json($this->fmt(Tukang::findOrFail($id)));
    }

    public function byLayanan()
    {
        $result = [];
        foreach (Layanan::orderBy('id_layanan')->get() as $lv) {
            $list = Tukang::where('status_aktif', 1)
                ->where('kategori', $lv->nama_layanan)->take(10)->get();
            if ($list->count()) {
                $result[] = ['layanan' => $lv, 'tukang' => $list->map(fn($t) => $this->fmt($t))->values()];
            }
        }
        return response()->json($result);
    }

    private function fmt(Tukang $t): array
    {
        return [
            'id_tukang'    => $t->id_tukang,
            'nama'         => $t->nama,
            'kategori'     => $t->kategori,
            'lokasi'       => $t->lokasi,
            'bio'          => $t->bio,
            'status_aktif' => (bool) $t->status_aktif,
            'foto_url'     => $t->foto ? url('storage/' . $t->foto) : null,
            'rating'       => 4.7,
        ];
    }
}
