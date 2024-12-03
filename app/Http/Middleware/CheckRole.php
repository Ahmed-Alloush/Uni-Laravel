<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user(); // Assuming the authenticated user is available

        // Check if user has any of the required roles
        if (!$user || !in_array($user->role->name, $roles)) {
            return response()->json(['message' => 'Forbidden: You don\'t have permission to access this resource.'], 403);
        }

        return $next($request);
    }
}
