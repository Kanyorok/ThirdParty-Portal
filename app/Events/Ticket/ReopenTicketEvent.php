<?php

namespace App\Events\Ticket;

use App\Models\Auth\User;
use App\Models\CRM\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReopenTicketEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Ticket $ticket, public User $actor, public string $reason)
    {
        //
    }
}
