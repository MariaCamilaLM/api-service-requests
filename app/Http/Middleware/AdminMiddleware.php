<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        Log::info(json_encode($user));
        Log::info($request->headers->get('Authorization'));
        // Check if the user is authenticated and is an admin
        if ($user && $user->profile === 'admin') {
            return $next($request);
        }

        return response()->json(['error' => 'Incorrect Profile'], 403);
    }
}
