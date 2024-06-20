<?php

namespace App\Event;

use App\Models\Load;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCargoNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $load;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Load $load)
    {
        $this->load = $load;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    // public function broadcastOn()
    // {
    //     return new PrivateChannel('channel-name');
    // }
}
