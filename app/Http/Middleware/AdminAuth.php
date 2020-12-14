<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $type = $request->user()->type;
        if (Auth::guard('api')->check() &&  (int) $type >= 1) {
            return $next($request);
        } else {
            $message = ["message" => "Permission Denied", "user"=> Auth::user()];
            return response($message, 401);
        }
    }
}
