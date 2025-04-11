<?php

namespace App\Events\Marketing;

use App\Models\MarketingList;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarketingListUploadedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public MarketingList $list, public string $Type, public string $file, public User $actor)
    {
        //
    }
}
