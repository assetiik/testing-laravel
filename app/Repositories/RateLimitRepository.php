<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;

class RateLimitRepository
{
    public function __construct(
        private readonly string $storagePath = '',
    ) {}

    private function path(): string
    {
        return $this->storagePath !== ''
            ? $this->storagePath
            : (string) config('contact.rate_limit.storage_path');
    }

    /**
     * @return array{attempts: int, first_attempt_at: int, expires_at: int}
     */
    public function get(string $key): array
    {
        $file = $this->filePath($key);

        if (! File::exists($file)) {
            return [
                'attempts' => 0,
                'first_attempt_at' => time(),
                'expires_at' => time() + (int) config('contact.rate_limit.decay_seconds'),
            ];
        }

        $decoded = json_decode(File::get($file), true);

        if (! is_array($decoded)) {
            return [
                'attempts' => 0,
                'first_attempt_at' => time(),
                'expires_at' => time() + (int) config('contact.rate_limit.decay_seconds'),
            ];
        }

        if (($decoded['expires_at'] ?? 0) < time()) {
            File::delete($file);

            return [
                'attempts' => 0,
                'first_attempt_at' => time(),
                'expires_at' => time() + (int) config('contact.rate_limit.decay_seconds'),
            ];
        }

        return [
            'attempts' => (int) ($decoded['attempts'] ?? 0),
            'first_attempt_at' => (int) ($decoded['first_attempt_at'] ?? time()),
            'expires_at' => (int) ($decoded['expires_at'] ?? time()),
        ];
    }

    public function hit(string $key): void
    {
        $current = $this->get($key);
        $current['attempts']++;

        $this->ensureDirectory();
        File::put($this->filePath($key), json_encode($current, JSON_PRETTY_PRINT));
    }

    public function remaining(string $key): int
    {
        $max = (int) config('contact.rate_limit.max_attempts');
        $current = $this->get($key);

        return max(0, $max - $current['attempts']);
    }

    public function retryAfter(string $key): int
    {
        $current = $this->get($key);

        return max(0, $current['expires_at'] - time());
    }

    public function tooManyAttempts(string $key): bool
    {
        $max = (int) config('contact.rate_limit.max_attempts');
        $current = $this->get($key);

        return $current['attempts'] >= $max;
    }

    private function filePath(string $key): string
    {
        return $this->path().'/'.hash('sha256', $key).'.json';
    }

    private function ensureDirectory(): void
    {
        if (! File::isDirectory($this->path())) {
            File::makeDirectory($this->path(), 0755, true);
        }
    }
}
