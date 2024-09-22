<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate(); 
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expiré, veuillez vous reconnecter.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalide, veuillez vous connecter.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token absent, veuillez vous connecter.'], 401);
        }
        return $next($request);
    }
}
