<?php

namespace Tests\Feature;

use App\Services\Ai\AiServiceInterface;
use App\Services\Ai\FallbackAiService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->app->bind(AiServiceInterface::class, FallbackAiService::class);

        config([
            'contact.storage.contacts' => storage_path('framework/testing/contacts.json'),
            'contact.storage.metrics' => storage_path('framework/testing/metrics.json'),
            'contact.rate_limit.storage_path' => storage_path('framework/testing/rate-limits'),
            'contact.rate_limit.max_attempts' => 3,
            'contact.rate_limit.decay_seconds' => 3600,
            'ai.enabled' => false,
        ]);

        File::ensureDirectoryExists(storage_path('framework/testing/rate-limits'));
        File::cleanDirectory(storage_path('framework/testing/rate-limits'));
        File::put(storage_path('framework/testing/contacts.json'), '[]');
        File::put(storage_path('framework/testing/metrics.json'), '{}');
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'ok');
    }

    public function test_contact_endpoint_validates_input(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'A',
            'phone' => '12',
            'email' => 'bad',
            'comment' => 'short',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'validation_error');
    }

    public function test_contact_endpoint_processes_request(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'phone' => '+7 999 123-45-67',
            'email' => 'ivan@example.com',
            'comment' => 'Здравствуйте! Интересует сотрудничество по Laravel-проекту.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'created_at',
                    'ai' => [
                        'sentiment',
                        'category',
                        'suggested_reply',
                        'used_fallback',
                    ],
                ],
            ]);

        $this->assertTrue($response->json('data.ai.used_fallback'));
    }

    /**
     * @return list<array{0: string}>
     */
    public static function invalidPhones(): array
    {
        return [
            ['12345'],
            ['-------'],
            ['+7 999 12'],
            ['abc-defg-hij'],
            ['+7 999 123 45 67 89 012'],
        ];
    }

    #[DataProvider('invalidPhones')]
    public function test_invalid_phone_is_rejected(string $phone): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'phone' => $phone,
            'email' => 'ivan@example.com',
            'comment' => 'Здравствуйте! Интересует сотрудничество по Laravel-проекту.',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function validPhones(): array
    {
        return [
            ['+7 999 123-45-67', '+79991234567'],
            ['8 (777) 123 45 67', '+77771234567'],
            ['+1 (415) 555-0132', '+14155550132'],
        ];
    }

    #[DataProvider('validPhones')]
    public function test_valid_phone_is_accepted_and_normalized(string $phone, string $expected): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'phone' => $phone,
            'email' => 'ivan@example.com',
            'comment' => 'Здравствуйте! Интересует сотрудничество по Laravel-проекту.',
        ])->assertCreated();

        $stored = json_decode(File::get(storage_path('framework/testing/contacts.json')), true);

        $this->assertSame($expected, $stored[0]['contact']['phone']);
    }

    public function test_metrics_endpoint_returns_stats(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'phone' => '+7 999 123-45-67',
            'email' => 'ivan@example.com',
            'comment' => 'Здравствуйте! Интересует сотрудничество по Laravel-проекту.',
        ])->assertCreated();

        $this->getJson('/api/metrics')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_contacts', 1);
    }

    public function test_rate_limiting_blocks_excess_requests(): void
    {
        $payload = [
            'name' => 'Иван Петров',
            'phone' => '+7 999 123-45-67',
            'email' => 'ivan@example.com',
            'comment' => 'Здравствуйте! Интересует сотрудничество по Laravel-проекту.',
        ];

        $this->postJson('/api/contact', $payload)->assertCreated();
        $this->postJson('/api/contact', $payload)->assertCreated();
        $this->postJson('/api/contact', $payload)->assertCreated();

        $this->postJson('/api/contact', $payload)
            ->assertStatus(429)
            ->assertJsonPath('error', 'rate_limit_exceeded');
    }
}
