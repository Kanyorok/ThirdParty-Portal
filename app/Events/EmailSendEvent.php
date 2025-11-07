<?php

namespace App\Events;

use App\Models\Communication\Email;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSendEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Email $crmEmail)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @ return array<int, \Illuminate\Broadcasting\Channel>
     *
     * public function broadcastOn(): array
     * {
     * return [
     * new PrivateChannel('channel-name'),
     * ];
     * } */
}
