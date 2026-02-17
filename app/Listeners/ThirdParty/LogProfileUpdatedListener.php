<?php

namespace App\Listeners\ThirdParty;

use App\Events\ThirdParty\ProfileUpdated;
use Illuminate\Support\Facades\Log;

class LogProfileUpdatedListener
{
    public function handle(ProfileUpdated $event): void
    {
        Log::info('Profile updated', [
            'third_party_id' => $event->thirdParty->Id,
            'user_id' => $event->user->Id,
            'changes' => $event->changes,
            'updated_at' => $event->thirdParty->ModifiedOn,
        ]);
    }
}
