<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'no_hp' => 'required|digits_between:8,15',
        ]);
        $user = User::create($request->only('nama', 'no_hp'));
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::with('orders')->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'nama' => 'sometimes|string|max:100',
            'no_hp' => 'sometimes|digits_between:8,15',
        ]);
        $user->update($request->only('nama', 'no_hp'));
        return response()->json($user);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User dihapus']);
    }
}
