<?php

namespace App\Exceptions\ThirdParty;

use Exception;
use Illuminate\Http\JsonResponse;

class ProfileCreationException extends Exception
{
    public function __construct(
        string $message = 'Failed to create profile',
        int $code = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'PROFILE_CREATION_FAILED',
        ];

        if (config('app.debug') && $this->previous) {
            $response['debug'] = [
                'error' => $this->previous->getMessage(),
                'file' => $this->previous->getFile(),
                'line' => $this->previous->getLine(),
            ];
        }

        return response()->json($response, $this->getCode());
    }
}
