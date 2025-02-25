<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Notification;


class NotificationPolicy
{
    /**
     * Determine whether the account can mark the notification as checked
     */
    public function check(Account $account, Notification $notification): bool
    {
        return ($account->id === $notification->emitted_to);
    }
}

