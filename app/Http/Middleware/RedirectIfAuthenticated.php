<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = Auth::user()) {
            return redirect($user->isAdmin() ? '/admin/dashboard' : '/mahasiswa/dashboard');
        }

        return $next($request);
    }
}
