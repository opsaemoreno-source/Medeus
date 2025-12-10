<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si no está autenticado o no es admin -> 403
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
