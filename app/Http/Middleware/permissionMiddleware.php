<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class permissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (Auth::guest()) {
            return redirect('/login');
        }
        if (! ($request->user()->can($permission) or $request->user()->hasrole('super-admin'))) {
           abort(403);
        }
        
        return $next($request);
    }
}
