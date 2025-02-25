<?php

namespace App\Events;

use App\Models\ForumMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostedMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    /**
     * Create a new event instance.
     */
    public function __construct($messageId)
    {
        $this->message = ForumMessage::with('getAccount.accountImage')->find($messageId);
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
    public function broadcastAs(){
        return 'posted-message';
    }
    public function broadcastWith()
    {
        return [
            'project_id' => $this->message->project,
            'message_id' => $this->message->id,
            'account_id' => $this->message->getAccount->id,
            'account_name' => $this->message->getAccount->name,
            'account_image' => $this->message->getAccount->getAccountImage(),
            'message_content' => $this->message->content,
            'message_date' => $this->message->create_date->format('d M Y, H:i'),
        ];
    }
}
