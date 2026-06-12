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

        $query = $this->visibleTukangQuery();
        if ($q) {
            $query->where(fn($q2) => $q2->where('nama', 'like', "%$q%")
                ->orWhere('kategori', 'like', "%$q%")
                ->orWhere('lokasi',   'like', "%$q%"));
        }
        if ($layanan) $query->where('kategori', $layanan);

        return $this->jsonNoCache($query->latest()->get()->map(fn($t) => $this->fmt($t)));
    }

    public function show($id)
    {
        return $this->jsonNoCache($this->fmt($this->visibleTukangQuery()->findOrFail($id)));
    }

    public function byLayanan()
    {
        $result = [];
        foreach (Layanan::orderBy('id_layanan')->get() as $lv) {
            $list = $this->visibleTukangQuery()
                ->where('kategori', $lv->nama_layanan)->take(10)->get();
            if ($list->count()) {
                $result[] = ['layanan' => $lv, 'tukang' => $list->map(fn($t) => $this->fmt($t))->values()];
            }
        }
        return $this->jsonNoCache($result);
    }

    private function fmt(Tukang $t): array
    {
        return [
            'id_tukang'    => $t->id_tukang,
            'nama'         => $t->nama,
            'kategori'     => $t->kategori,
            'lokasi'       => $t->lokasi,
            'alamat'       => $t->alamat,
            'bio'          => $t->bio,
            'no_hp'        => $t->no_hp,
            'tarif'        => $t->tarif,
            'status_aktif' => (bool) $t->status_aktif,
            'foto_url'     => $t->foto ? url('storage/' . $t->foto) : null,
            'rating'       => 4.7,
            'latitude'     => $t->latitude ? (float) $t->latitude : null,
            'longitude'    => $t->longitude ? (float) $t->longitude : null,
            'updated_at'    => $t->updated_at?->toISOString(),
        ];
    }

    private function visibleTukangQuery()
    {
        return Tukang::where('status_aktif', 1)
            ->where('status_verifikasi', 'verified');
    }

    private function jsonNoCache($data, int $status = 200)
    {
        return response()->json($data, $status)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
