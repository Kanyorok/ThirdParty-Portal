<?php

namespace App\Listeners\ThirdParty;

use App\Events\ThirdParty\ProfileCreated;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\Log;

class LogProfileCreatedlistener
{
    public function handle(ProfileCreated $event): void
    {
        $thirdParty = ThirdParties::find($event->thirdPartyId);

        Log::info('Profile created', [
            'third_party_id' => $event->thirdPartyId,
            'user_id' => $event->userId,
            'profile_type' => $event->profileType->value,
            'third_party_name' => $thirdParty?->ThirdPartyName,
            'created_at' => $thirdParty?->CreatedOn,
        ]);
    }
}
