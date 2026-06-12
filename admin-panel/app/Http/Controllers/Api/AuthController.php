<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'no_hp'    => 'required|digits_between:8,15|unique:user,no_hp',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'nama'     => $request->nama,
            'no_hp'    => $request->no_hp,
            'password' => Hash::make($request->password),
        ]);
        $user->load('badges');

        $token = $user->createToken('tomas-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'no_hp'    => 'required|digits_between:8,15',
            'password' => 'required|string',
        ]);

        $user = User::where('no_hp', $request->no_hp)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'No HP atau password salah.'], 401);
        }
        $user->load('badges');

        $token = $user->createToken('tomas-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('badges'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nama'             => 'sometimes|string|max:100',
            'no_hp'            => 'sometimes|digits_between:8,15|unique:user,no_hp,' . $user->id_user . ',id_user',
            'alamat'           => 'sometimes|nullable|string|max:255',
            'password'         => 'sometimes|string|min:6|confirmed',
        ]);

        $data = $request->only(['nama', 'no_hp', 'alamat']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user->fresh()->load('badges'));
    }
}
