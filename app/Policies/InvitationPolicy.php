<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Invitation;
use Illuminate\Auth\Access\Response;

class InvitationPolicy
{
   
    /**
     * Determine whether the user can accept the invite.
     */
    public function accept(Account $account, Invitation $invitation): bool
    {
        if (!$account->id === $invitation->account) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can accept the invite.
     */
    public function decline(Account $account, Invitation $invitation): bool
    {
        if (!$account->id === $invitation->account) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return false;
        }
        return true;
    }
}
