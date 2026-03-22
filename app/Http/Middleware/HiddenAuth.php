<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HiddenAuth
{
    public function handle(Request $request, Closure $next, string $role = null)
    {
        // Check if user is authenticated in hidden session
        if (!session()->has('hidden_user_id')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return response()->json(['redirect' => route('news.index')], 401);
        }

        // Single DB lookup for the session user, reused for role check and request injection
        $user = \App\Models\User::find(session('hidden_user_id'));

        // Role check
        if ($role === 'admin') {
            if (!$user || !$user->isAdmin()) {
                abort(403, 'Admin access required.');
            }
        }

        // Inject user into request (no second query)
        $request->merge(['hidden_user' => $user]);

        return $next($request);
    }
}
