<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IngresoInicialMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (session()->has('empresa')) {
            $empresaId = session('empresa')->id;
            if (empty($empresaId)) {
                return redirect()->route('empresa.select');
            }
        }else{    
            return redirect()->route('empresa.select');
        }

        if (Auth()->user()->cambio_password === 1) {
            return redirect()->route('profile.password')
                ->with('noticia', 'Atención!!! debe cambiar la clave, ingresando una nueva que cumpla los criterios de seguridad.');
        }

        return $next($request);

    }
}
