<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('user_id')) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'no_hp'    => 'required|digits_between:8,15',
            'password' => 'required|string',
        ]);

        $user = User::where('no_hp', $request->no_hp)->first();

        if (!$user || !Hash::check($request->password, $user->password ?? '')) {
            return back()->with('error', 'No HP atau password salah.')->withInput();
        }

        session([
            'user_id'   => $user->id_user,
            'user_nama' => $user->nama,
            'user_hp'   => $user->no_hp,
        ]);

        return redirect()->route('home');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

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

        session([
            'user_id'   => $user->id_user,
            'user_nama' => $user->nama,
            'user_hp'   => $user->no_hp,
        ]);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}

