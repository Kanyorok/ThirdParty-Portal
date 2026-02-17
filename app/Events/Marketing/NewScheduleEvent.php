<?php

namespace App\Events\Marketing;

use App\Models\Auth\User;
use App\Models\CRM\Schedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewScheduleEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Schedule $schedule, public User $actor)
    {
    }
}
