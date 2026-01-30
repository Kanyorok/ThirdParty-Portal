<?php

namespace App\Events\Marketing;

use App\Models\Auth\User;
use App\Models\CRM\Campaign;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignRunEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Campaign $campaign, public User $actor)
    {
    }
}
