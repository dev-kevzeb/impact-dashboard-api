<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

/**
 * JWT Middleware - Validates JWT tokens on protected routes
 * 
 * This middleware:
 * - Validates JWT token presence and format
 * - Checks token expiration
 * - Loads authenticated user into request
 * - Returns 401 for invalid/expired/missing tokens
 */
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

            if (!$user) {
                return ApiResponse::error('User not found', 401);
            }

            // Attach user to request for controllers
            $request->merge(['auth_user' => $user]);
        } catch (TokenExpiredException $e) {
            return ApiResponse::error('Token has expired', 401);
        } catch (TokenInvalidException $e) {
            return ApiResponse::error('Token is invalid', 401);
        } catch (JWTException $e) {
            return ApiResponse::error('Token not provided', 401);
        } catch (\Exception $e) {
            return ApiResponse::error('Authorization error', 401);
        }

        return $next($request);
    }
}
