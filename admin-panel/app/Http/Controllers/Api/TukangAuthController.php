<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tukang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TukangAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:tukang,username',
            'no_hp'    => 'required|digits_between:8,15|unique:tukang,no_hp',
            'no_ktp'   => 'nullable|digits:16',
            'alamat'   => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude'=> 'nullable|numeric',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tukang = Tukang::create([
            'nama'      => $request->nama,
            'username'  => $request->username,
            'no_hp'     => $request->no_hp,
            'no_ktp'    => $request->no_ktp,
            'alamat'    => $request->alamat,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'password'  => Hash::make($request->password),
            'status_aktif' => 0,
            'status_verifikasi' => 'pending',
        ]);

        return response()->json([
            'tukang' => $tukang,
            'token' => base64_encode($tukang->username . '|tukang'),
            'message' => 'Pendaftaran berhasil, menunggu verifikasi admin.'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tukang = Tukang::where('username', $request->username)->first();
        if (!$tukang || !Hash::check($request->password, $tukang->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }
        if ($tukang->status_verifikasi === 'rejected') {
            return response()->json([
                'message' => 'Register ditolak oleh admin. Silakan daftar ulang atau hubungi admin.',
                'reason' => $tukang->rejection_reason ?? null,
            ], 403);
        }
        if ($tukang->status_verifikasi !== 'verified') {
            return response()->json(['message' => 'Akun belum diverifikasi admin.'], 403);
        }
        // Token dummy, bisa pakai Sanctum jika mau
        return response()->json([
            'tukang' => $tukang,
            'token' => base64_encode($tukang->username . '|tukang'),
        ]);
    }
}
