<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si no está autenticado o no es admin -> 403
        if (! Auth::check() || ! Auth::user()->is_admin) {
            Log::warning('Acceso no autorizado a ruta de administrador', [
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
