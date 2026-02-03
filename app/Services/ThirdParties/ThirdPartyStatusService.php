<?php

namespace App\Services\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\Log;

class ThirdPartyStatusService
{
    public const CODE_ID = 'ThirdPartyStatus';

    public function applyStatus(ThirdParties $thirdParty, ThirdPartyStatusEnum $status): ThirdParties
    {
        $codeDetail = CodeDetail::query()
            ->where('CodeID', self::CODE_ID)
            ->where('Value', $status->value)
            ->first();

        if (! $codeDetail) {
            Log::error('ThirdPartyStatusService: missing CodeDetail for status update', [
                'code_id' => self::CODE_ID,
                'value' => $status->value,
            ]);

            throw new \RuntimeException("CodeDetail not found for {$status->value} under " . self::CODE_ID);
        }

        if ($thirdParty->Status === $codeDetail->Id) {
            return $thirdParty->setRelation('status', $codeDetail);
        }

        $thirdParty->Status = $codeDetail->Id;
        $thirdParty->save();
        $thirdParty->setRelation('status', $codeDetail);

        return $thirdParty;
    }
}
