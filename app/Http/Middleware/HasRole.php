<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next, int $role)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) return ResponseFormatter::error(NULL, 400, 'Token is invalid');

            if ($e instanceof TokenExpiredException) {
                return ResponseFormatter::error(NULL, 400, 'Token is expired');
            } else {
                return ResponseFormatter::error(NULL, 500, 'Authorization token not found');
            }
        }
        if ($user->role_id !== $role) return ResponseFormatter::error(NULL, 400, 'You are not authorized to access this resource');
        return $next($request);
    }
}
