<?php

namespace App\Events\Marketing;

use App\Models\Auth\User;
use App\Models\CRM\MarketingPlanner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlannerSubmitEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public MarketingPlanner $planner, public User $actor)
    {
    }
}
