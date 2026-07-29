<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class RateLimitExceededException extends Exception
{
    public function __construct(
        public readonly int $retryAfterSeconds,
        string $message = 'Too many contact requests. Please try again later.',
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error' => 'rate_limit_exceeded',
            'retry_after' => $this->retryAfterSeconds,
        ], 429)->header('Retry-After', (string) $this->retryAfterSeconds);
    }
}
