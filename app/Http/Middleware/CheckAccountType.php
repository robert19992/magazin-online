<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccountType
{
    public function handle(Request $request, Closure $next, string $type)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ($type === 'supplier' && !auth()->user()->isSupplier()) {
            abort(403, 'Accesul este permis doar furnizorilor.');
        }

        if ($type === 'client' && !auth()->user()->isClient()) {
            abort(403, 'Accesul este permis doar clienților.');
        }

        return $next($request);
    }
} 