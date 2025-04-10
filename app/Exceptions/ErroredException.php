<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class ErroredException extends Exception
{
    protected $code = 400;

    public function __construct(string $message = "an expected error occurred.", ?Throwable $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }

    public function toJson(): JsonResponse
    {
        return response()->json(['message' => $this->message], $this->code);
    }
}
