<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 

class CheckAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user_id  = Auth::id();
        $id       = $request->route()->parameter('id');
        $owner_id = $request->route()->parameter('owner_id');

        // Check if the user is authenticated
        if ($user_id != $owner_id && $user_id != $id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized User!'
            ], ResponseCode["Unauthorized"]);
        }

        return $next($request);
    }
}
