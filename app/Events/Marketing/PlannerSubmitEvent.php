<?php

namespace App\Events\Marketing;

use App\Models\MarketingPlanner;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlannerSubmitEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public MarketingPlanner $planner, public User $actor)
    {
    }
}
