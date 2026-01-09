<?php

namespace App\Events\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ProfileCreatedEvent
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $thirdPartyId,
        public int $userId,
        public ThirdPartyTypeEnum $profileType
    ) {}
}
