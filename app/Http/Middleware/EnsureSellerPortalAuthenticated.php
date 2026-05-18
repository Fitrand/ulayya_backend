<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerPortalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('seller.login');
        }

        if (! $user->is_admin) {
            abort(403, 'Akses portal penjual hanya untuk admin/seller.');
        }

        return $next($request);
    }
}