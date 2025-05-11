<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isClient()) {
            abort(403, 'Acces interzis. Doar clienții pot accesa această pagină.');
        }

        return $next($request);
    }
} 