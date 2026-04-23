<?php

namespace App\Events;

use App\Models\Advertisement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdvertisementSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $advertisement;
    public $receiverRole;

    public function __construct(Advertisement $advertisement, $receiverRole)
    {
        $this->advertisement = $advertisement;
        $this->receiverRole = $receiverRole;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('advertisement-notify.' . $this->receiverRole);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->advertisement->id,
            'title' => $this->advertisement->title,
            'status' => $this->advertisement->status,
            'message' => "Advertisement has been updated.",
        ];
    }
}
