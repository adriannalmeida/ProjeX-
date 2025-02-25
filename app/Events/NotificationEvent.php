<?php

namespace App\Events;

use App\Models\Account;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    private $accountId;

    /**
     * Create a new event instance.
     */

    public function __construct($notificationId, $accountId = null)
    {
        try {
            // Attempt to find the notification, or fail if it doesn't exist
            $this->notification = Notification::find($notificationId);
            $this->accountId = $accountId; // Custom account ID for specific notifications
        } catch (\Exception $e) {
            $this->notification = null;
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
        return 'project-notification';
    }

    public function broadcastWith()
    {
        if ($this->notification->type->value == 'Assigned_Task') {
            return [
                'notification_id' => $this->notification->id,
                'notification_description' => $this->notification->description(),
                'notification_date' => $this->notification->create_date->format('F j, Y'),
                'account_id' => $this->notification->emitted_to,
            ];
        }
        else{
            return [
                'notification_id' => $this->notification->id,
                'notification_description' => $this->notification->description(),
                'notification_date' => $this->notification->create_date,
                'account_id' => $this->accountId,
            ];
        }
    }
}