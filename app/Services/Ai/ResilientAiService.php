<?php

namespace App\Services\Ai;

use App\DTOs\AiAnalysisResult;
use App\DTOs\ContactData;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Decorator that tries the primary AI provider and falls back gracefully.
 */
class ResilientAiService implements AiServiceInterface
{
    public function __construct(
        private readonly OpenAiCompatibleService $primary,
        private readonly FallbackAiService $fallback,
    ) {}

    public function analyze(ContactData $contact): AiAnalysisResult
    {
        if (! config('ai.enabled')) {
            Log::channel('api')->info('AI disabled via config, using fallback.');

            return $this->fallback->analyze($contact);
        }

        try {
            return $this->primary->analyze($contact);
        } catch (Throwable $exception) {
            Log::channel('api')->warning('AI provider unavailable, using fallback.', [
                'provider' => config('ai.provider'),
                'model' => config('ai.model'),
                'error' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return $this->fallback->analyze($contact);
        }
    }
}
