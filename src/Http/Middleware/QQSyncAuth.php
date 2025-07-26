<?php

namespace Lynnezra\Seat\QQSync\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lynnezra\Seat\QQSync\Models\QQBotConfig;

class QQSyncAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }
        
        $config = QQBotConfig::where('api_token', $token)
            ->where('is_active', true)
            ->first();
            
        if (!$config) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        return $next($request);
    }
}