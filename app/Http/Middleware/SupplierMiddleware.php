<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SupplierMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSupplier()) {
            abort(403, 'Acces interzis. Doar furnizorii pot accesa această pagină.');
        }

        return $next($request);
    }
} 