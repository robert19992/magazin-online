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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $userRole = $request->user()->role;
        
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                $allowedRoles = explode(',', $role);
                if (in_array($userRole, $allowedRoles)) {
                    return $next($request);
                }
            } else if ($userRole === $role) {
                return $next($request);
            }
        }

        abort(403, 'Nu aveți permisiunea de a accesa această pagină.');
    }
}
