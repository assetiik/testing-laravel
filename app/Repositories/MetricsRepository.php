<?php

namespace App\Repositories;

use App\DTOs\AiAnalysisResult;
use Illuminate\Support\Facades\File;

class MetricsRepository extends JsonFileRepository
{
    public function __construct()
    {
        parent::__construct(config('contact.storage.metrics'));
    }

    public function increment(AiAnalysisResult $analysis): void
    {
        $metrics = $this->defaultMetrics();
        $existing = $this->readObject();

        $metrics = array_replace_recursive($metrics, $existing);

        $metrics['total_contacts'] = (int) $metrics['total_contacts'] + 1;
        $metrics['last_contact_at'] = now()->toIso8601String();

        $sentiment = $analysis->sentiment;
        $category = $analysis->category;

        $metrics['by_sentiment'][$sentiment] = (int) ($metrics['by_sentiment'][$sentiment] ?? 0) + 1;
        $metrics['by_category'][$category] = (int) ($metrics['by_category'][$category] ?? 0) + 1;

        if ($analysis->usedFallback) {
            $metrics['ai_fallback_count'] = (int) $metrics['ai_fallback_count'] + 1;
        } else {
            $metrics['ai_success_count'] = (int) $metrics['ai_success_count'] + 1;
        }

        $this->write($metrics);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return array_replace_recursive($this->defaultMetrics(), $this->readObject());
    }

    /**
     * @return array<string, mixed>
     */
    private function readObject(): array
    {
        $this->ensureDirectoryExists();

        if (! File::exists($this->filePath)) {
            File::put($this->filePath, '{}');

            return [];
        }

        $contents = File::get($this->filePath);
        $decoded = json_decode($contents ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultMetrics(): array
    {
        return [
            'total_contacts' => 0,
            'ai_success_count' => 0,
            'ai_fallback_count' => 0,
            'last_contact_at' => null,
            'by_sentiment' => [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
            ],
            'by_category' => [
                'job_offer' => 0,
                'collaboration' => 0,
                'question' => 0,
                'feedback' => 0,
                'spam' => 0,
                'other' => 0,
            ],
        ];
    }
}
