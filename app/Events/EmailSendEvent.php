<?php

namespace App\Events;

use App\Models\CrmEmail;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSendEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public CrmEmail $crmEmail)
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
