<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RestrictIpPort
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define allowed origins with ports
        $allowedOrigins = [
            'https://parknjetseatac.com:2896',
            'http://parknjetseatac.com:2896',
        ];
        
        $allowedPorts = [
            '2896',
        ];

        // Get the Origin header
        $origin = $request->header('Origin');
        $port = $request->server('REMOTE_PORT');
        Log::info("requested origin " . $origin . " requested port " . $port);

        // Check if the request has a valid origin
        if (!$origin || !in_array($origin, $allowedOrigins) && !in_array($port, $allowedPorts)) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        return $next($request);
    }
}
