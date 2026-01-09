<?php

namespace App\Exceptions\ThirdParty;

use Exception;
use Illuminate\Http\JsonResponse;

class ProfileNotFoundException extends Exception
{
    public function __construct(
        string $message = 'Profile not found',
        int $code = 404,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'PROFILE_NOT_FOUND',
        ], $this->getCode());
    }
}
