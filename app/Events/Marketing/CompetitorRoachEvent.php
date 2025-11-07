<?php

namespace App\Events\Marketing;

use App\Models\ThirdParies\Competitor;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitorRoachEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Competitor $competitor, public bool $clear)
    {
    }
}
