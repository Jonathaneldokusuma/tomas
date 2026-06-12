<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Layanan::all());
    }

    public function store(Request $request)
    {
        $request->validate(['nama_layanan' => 'required|string|max:100']);
        $layanan = Layanan::create($request->only('nama_layanan'));
        return response()->json($layanan, 201);
    }

    public function show($id)
    {
        $layanan = Layanan::findOrFail($id);
        return response()->json($layanan);
    }

    public function update(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);
        $request->validate(['nama_layanan' => 'required|string|max:100']);
        $layanan->update($request->only('nama_layanan'));
        return response()->json($layanan);
    }

    public function destroy($id)
    {
        Layanan::findOrFail($id)->delete();
        return response()->json(['message' => 'Layanan dihapus']);
    }
}
