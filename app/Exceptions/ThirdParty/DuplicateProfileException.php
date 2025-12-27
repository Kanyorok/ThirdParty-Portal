<?php

namespace App\Exceptions\ThirdParty;

use Exception;
use Illuminate\Http\JsonResponse;

class DuplicateProfileException extends Exception
{
    public function __construct(
        string $profileType,
        int $code = 409,
        ?\Throwable $previous = null
    ) {
        $message = "You already have a {$profileType} profile. Multiple profiles of the same type are not allowed.";
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'DUPLICATE_PROFILE',
        ], $this->getCode());
    }
}
