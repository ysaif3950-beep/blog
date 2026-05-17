<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessTokenSessionIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if (! $plainTextToken) {
            return $next($request);
        }

        $accessTokenModel = Sanctum::$personalAccessTokenModel;
        $accessToken = $accessTokenModel::findToken($plainTextToken);

        if (! $accessToken) {
            return $next($request);
        }

        $lastActivity = $accessToken->last_used_at ?? $accessToken->created_at;

        if ($lastActivity->lt(now()->subMinutes((int) config('sanctum.refresh_ttl', 30)))) {
            $accessToken->delete();

            return response()->json([
                'success' => false,
                'message' => 'Session expired, please login again',
            ], 401);
        }

        return $next($request);
    }
}
