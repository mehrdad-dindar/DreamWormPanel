<?php

namespace App\Events;

use App\Models\User;
use App\Models\WorkSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkSessionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Queueable;

    public WorkSession $workSession;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct($workSession)
    {
        $this->workSession = $workSession;
        $this->user = User::find($workSession->user_id);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
