<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Mark a notification as checked.
     */
    public function check(Notification $notification)
    {
        try {
            $this->authorize('check', $notification);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        $notification->update(['checked' => true]);

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Notification checked.',
        ]);
        return redirect()->back()->with('success', 'Notification checked.');
    }
}
