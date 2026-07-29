<?php

namespace App\Repositories;

use App\DTOs\AiAnalysisResult;
use App\DTOs\ContactData;
use Illuminate\Support\Str;

class ContactRepository extends JsonFileRepository
{
    public function __construct()
    {
        parent::__construct(config('contact.storage.contacts'));
    }

    /**
     * @return array<string, mixed>
     */
    public function store(ContactData $contact, AiAnalysisResult $analysis): array
    {
        $records = $this->read();

        $record = [
            'id' => (string) Str::uuid(),
            'created_at' => now()->toIso8601String(),
            'contact' => $contact->toArray(),
            'ai' => $analysis->toArray(),
        ];

        $records[] = $record;
        $this->write($records);

        return $record;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        /** @var list<array<string, mixed>> $records */
        $records = array_values($this->read());

        return $records;
    }

    public function count(): int
    {
        return count($this->read());
    }
}
