<?php

namespace App\Exceptions\ThirdParty;

use Exception;
use Illuminate\Http\JsonResponse;

class ProfileNotApprovedException extends Exception
{
    public function __construct(
        string $message = 'This action requires an approved profile',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'PROFILE_NOT_APPROVED',
        ], $this->getCode());
    }
}
