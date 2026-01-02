<?php

namespace App\Exceptions\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use Exception;
use Illuminate\Http\JsonResponse;

class InvalidProfileTransitionException extends Exception
{
    public function __construct(
        ThirdPartyApprovalStatusEnum $currentStatus,
        ThirdPartyApprovalStatusEnum $newStatus,
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        $message = "Invalid status transition from {$currentStatus->label()} to {$newStatus->label()}.";
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'INVALID_PROFILE_TRANSITION',
        ], $this->getCode());
    }
}
