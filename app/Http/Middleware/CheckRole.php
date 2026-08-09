<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $userRole = $user->role?->name;

        if (!in_array($userRole, $roles)) {
            // User is logged in but tried to open an area outside their role.
            // Send them to their own dashboard instead of a dead-end 403.
            $target = $user->dashboardRoute();

            if (!$request->routeIs($target)) {
                return redirect()->route($target)
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut. Diarahkan ke dashboard Anda.');
            }

            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
