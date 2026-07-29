<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use RuntimeException;

class JsonFileRepository
{
    public function __construct(
        protected readonly string $filePath,
    ) {}

    /**
     * @return array<int|string, mixed>
     */
    protected function read(): array
    {
        $this->ensureFileExists();

        $contents = File::get($this->filePath);

        if ($contents === '' || $contents === false) {
            return [];
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in file: {$this->filePath}");
        }

        return $decoded;
    }

    /**
     * @param  array<int|string, mixed>  $data
     */
    protected function write(array $data): void
    {
        $this->ensureDirectoryExists();

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException("Unable to encode JSON for file: {$this->filePath}");
        }

        File::put($this->filePath, $json);
    }

    protected function ensureFileExists(): void
    {
        $this->ensureDirectoryExists();

        if (! File::exists($this->filePath)) {
            File::put($this->filePath, '[]');
        }
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = dirname($this->filePath);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
