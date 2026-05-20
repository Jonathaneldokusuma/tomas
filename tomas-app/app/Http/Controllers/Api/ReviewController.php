<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $id_order)
    {
        $order = Order::where('id_order', $id_order)
            ->where('id_user', $request->user()->id_user)
            ->firstOrFail();

        if ($order->review) {
            return response()->json(['message' => 'Review sudah ada.'], 409);
        }

        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);

        $review = Review::create([
            'id_order' => $order->id_order,
            'rating'   => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return response()->json($review, 201);
    }
}
