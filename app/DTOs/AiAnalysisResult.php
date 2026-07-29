<?php

namespace App\DTOs;

readonly class AiAnalysisResult
{
    public function __construct(
        public string $sentiment,
        public float $sentimentScore,
        public string $category,
        public string $priority,
        public string $summary,
        public string $suggestedReply,
        public bool $usedFallback = false,
        public ?string $provider = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload, bool $usedFallback = false, ?string $provider = null): self
    {
        return new self(
            sentiment: (string) ($payload['sentiment'] ?? 'neutral'),
            sentimentScore: (float) ($payload['sentiment_score'] ?? 0.0),
            category: (string) ($payload['category'] ?? 'other'),
            priority: (string) ($payload['priority'] ?? 'medium'),
            summary: (string) ($payload['summary'] ?? ''),
            suggestedReply: (string) ($payload['suggested_reply'] ?? ''),
            usedFallback: $usedFallback,
            provider: $provider,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sentiment' => $this->sentiment,
            'sentiment_score' => $this->sentimentScore,
            'category' => $this->category,
            'priority' => $this->priority,
            'summary' => $this->summary,
            'suggested_reply' => $this->suggestedReply,
            'used_fallback' => $this->usedFallback,
            'provider' => $this->provider,
        ];
    }
}
