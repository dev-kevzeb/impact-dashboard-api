<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckScope Middleware - Simple modular scope validation
 * 
 * Validates that authenticated user has required scope(s) for a module.
 * Scopes are stored in JWT custom claims during login/register.
 * 
 * Usage in routes:
 * Route::get('/users', [UserController::class, 'index'])->middleware('scope:users');
 * Route::post('/donors', [DonorController::class, 'store'])->middleware('scope:donors:write');
 * 
 * Scope format: {module}:{permission}
 * - {module}: donors, users, projects, etc.
 * - {permission}: read, write, delete (optional, default: read)
 * 
 * Examples:
 * - 'donors' or 'donors:read' - Can read donors
 * - 'donors:write' - Can create/update donors
 * - 'donors:delete' - Can delete donors (not used yet)
 */
class CheckScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $requiredScope  The scope required (e.g., 'donors', 'donors:write')
     */
    public function handle(Request $request, Closure $next, string $requiredScope): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return ApiResponse::error('Not authenticated', 401)->send();
        }

        // Get user scopes from JWT token
        $token = auth('api')->getToken();
        $payload = auth('api')->getPayload($token);
        $userScopes = $payload->get('scopes', []);

        // Check if user has required scope
        if (!$this->hasScope($userScopes, $requiredScope)) {
            return ApiResponse::error(
                "Insufficient permissions. Required scope: {$requiredScope}",
                403
            )->send();
        }

        return $next($request);
    }

    /**
     * Check if user has the required scope
     *
     * @param array $userScopes  Scopes from JWT token
     * @param string $requiredScope  Required scope to check
     * @return bool
     */
    private function hasScope(array $userScopes, string $requiredScope): bool
    {
        // Parse required scope (e.g., 'donors:write' -> ['donors', 'write'])
        $parts = explode(':', $requiredScope);
        $module = $parts[0];
        $permission = $parts[1] ?? 'read';  // Default to 'read'

        // Check if user has exact scope
        $fullScope = "{$module}:{$permission}";
        if (in_array($fullScope, $userScopes)) {
            return true;
        }

        // Check if user has wildcard for module (e.g., 'donors:*')
        if (in_array("{$module}:*", $userScopes)) {
            return true;
        }

        // Check if user has global admin scope
        if (in_array('*:*', $userScopes)) {
            return true;
        }

        return false;
    }
}
