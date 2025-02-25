<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostedTaskComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    /**
     * Create a new event instance.
     */
    public function __construct($commentId)
    {
        $this->comment = Comment::with('getAccount.accountImage')->find($commentId);
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
        return 'posted-comment';
    }
    public function broadcastWith()
    {
        return [
            'task_id' => $this->comment->task,
            'project_id' => $this->comment->project,
            'comment_id' => $this->comment->id,
            'get_account' => [
                'name' => $this->comment->getAccount->name,
            ],
            'image_path' => $this->comment->getAccount->getAccountImage(),
            'content' => $this->comment->content,
            'create_date' => $this->comment->create_date->format('Y-m-d H:i:s'), // Return raw date for JavaScript formatting
        ];

    }
}
