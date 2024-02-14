<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'API token is missing'], 401);
        }

        $hashedToken = hash('sha256', $token);

        $user = DB::table('users')
            ->leftJoin('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
            ->where('personal_access_tokens.token', $hashedToken)
            ->where('personal_access_tokens.token', $hashedToken)
            ->select('users.*')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid API token'], 401);
        }

        return $next($request);
    }
}
