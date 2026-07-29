<?php

namespace App\Console\Commands;

use App\DTOs\ContactData;
use App\Services\Ai\OpenAiCompatibleService;
use Illuminate\Console\Command;
use Throwable;

class TestAiCommand extends Command
{
    protected $signature = 'ai:test {comment? : Text to analyze}';

    protected $description = 'Call the configured AI provider directly, bypassing the fallback';

    public function handle(OpenAiCompatibleService $ai): int
    {
        $comment = $this->argument('comment')
            ?? 'Здравствуйте! Интересует сотрудничество по Laravel-проекту, есть бюджет.';

        $this->line('Provider: '.config('ai.provider').' ('.config('ai.model').')');

        $contact = new ContactData(
            name: 'Test User',
            phone: '+79991234567',
            email: 'test@example.com',
            comment: $comment,
            ip: '127.0.0.1',
            userAgent: 'artisan',
        );

        try {
            $result = $ai->analyze($contact);
        } catch (Throwable $exception) {
            $this->error('AI call failed: '.$exception->getMessage());
            $this->line('The API itself keeps working — FallbackAiService would take over.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(['Field', 'Value'], [
            ['sentiment', $result->sentiment],
            ['sentiment_score', $result->sentimentScore],
            ['category', $result->category],
            ['priority', $result->priority],
            ['summary', $result->summary],
            ['suggested_reply', $result->suggestedReply],
        ]);

        return self::SUCCESS;
    }
}
