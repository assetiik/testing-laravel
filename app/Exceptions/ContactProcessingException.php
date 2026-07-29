<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ContactProcessingException extends Exception
{
    public function __construct(
        string $message = 'Failed to process contact request.',
        public readonly int $statusCode = 500,
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error' => 'contact_processing_failed',
        ], $this->statusCode);
    }
}
