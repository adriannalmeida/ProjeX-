<?php

namespace App\Events;

use App\Models\Account;
use App\Models\Invitation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvitedToProject implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invitation;
    private $account;
    /**
     * Create a new event instance.
     */

    public function __construct($invitationId)
    {
        try {
            // Attempt to find the invitation, or fail if it doesn't exist
            $this->invitation = Invitation::with('getProject', 'account')->find($invitationId);
            $this->account = Account::findOrFail($this->invitation->account);
        } catch (\Exception $e) {
            $this->invitation = null;
            return;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return ['projeX'];
    }

    public function broadcastAs()
    {
        return 'invited-to-project';
    }

    public function broadcastWith()
    {
        return [
            'project_name' => $this->invitation->getProject->name,
            'project_id' => $this->invitation->getProject->id,
            'invitation_id' => $this->invitation->id,
            'invitation_status' => $this->invitation->accepted,
            'account_id' => $this->account->id,
            'account_name' => $this->account->name,
            'account_email' => $this->account->email,
        ];
    }
}

