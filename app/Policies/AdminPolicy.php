<?php

namespace App\Policies;
use App\Models\Account;

class AdminPolicy
{
    /**
     * Determine whether the user is an admin.
     */
    public function isAdmin(Account $account)
    {
        return $account->admin;
    }
}