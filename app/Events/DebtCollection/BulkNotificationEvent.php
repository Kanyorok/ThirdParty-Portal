<?php

namespace App\Events\DebtCollection;

use App\Models\BulkNotification;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class BulkNotificationEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public BulkNotification $bulkNotification, public User $actor, public array $attributes, public Carbon $dated)
    {
    }
}
