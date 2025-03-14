<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTokenExpired
{
    public function handle(Request $request, Closure $next)
    {
        // Проверяем аутентифицированного пользователя
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Текущий токен (Sanctum)
        $token = $user->currentAccessToken();
        if (!$token) {
            return response()->json(['message' => 'No current token found'], 401);
        }

        // Если есть expires_at и оно в прошлом => просрочен
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}
