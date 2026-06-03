<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('admin_token');
        $expected = hash_hmac('sha256', 'admin_authenticated', config('app.key'));
        if (!$token || !hash_equals($expected, $token)) {
            return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return $next($request);
    }
}
