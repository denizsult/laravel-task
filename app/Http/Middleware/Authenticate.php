<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request)
    {
        // Eğer request API ise JSON döndür
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json(['message' => 'Unauthenticated.'], 401));
        }

        // Web request için normal redirect
        return route('login');
    }
}
