<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOrGuest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->admin) {
            // If authenticated and not an admin, deny access
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back()->with('error', 'Access denied.');
        }
        return $next($request);
    }
}