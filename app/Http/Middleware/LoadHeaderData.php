<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Invitation;

class LoadHeaderData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $invitations = Invitation::where('account', Auth::id())
                ->where('accepted', false)
                ->take(3) // Limit to the latest 3 invitations
                ->get();

            $notifications = Notification::where('emitted_to', Auth::id())
                ->where('checked', false)
                ->take(3) // Limit to the latest 3 notifications
                ->orderBy('create_date', 'desc')
                ->get();

            $invitationCount = Invitation::where('account', Auth::id())
                ->where('accepted', false)
                ->count();

            $notificationCount = Notification::where('emitted_to', Auth::id())
                ->where('checked', false)
                ->count();
            View::share([
                'headerInvitations' => $invitations,
                'headerNotifications' => $notifications,
                'invitationCount' => $invitationCount,
                'notificationCount' => $notificationCount,
            ]);
        }
        return $next($request);
    }

}
