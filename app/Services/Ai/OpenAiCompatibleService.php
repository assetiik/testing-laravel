<?php

namespace App\Services\Ai;

use App\DTOs\AiAnalysisResult;
use App\DTOs\ContactData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Talks to any provider exposing the OpenAI "chat/completions" protocol
 * (OpenAI, Groq, OpenRouter, local models). The provider is chosen in config/ai.php.
 */
class OpenAiCompatibleService implements AiServiceInterface
{
    public function analyze(ContactData $contact): AiAnalysisResult
    {
        $apiKey = trim((string) config('ai.api_key'));
        $provider = (string) config('ai.provider');
        $baseUrl = rtrim((string) config('ai.base_url'), '/');
        $endpoint = $baseUrl.'/chat/completions';

        if ($apiKey === '') {
            throw new RuntimeException("AI API key is not configured for provider [{$provider}].");
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout((int) config('ai.timeout', 15))
                ->connectTimeout(10)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'User-Agent' => config('app.name', 'DevLandingAPI').'/1.0',
                ])
                ->post($endpoint, [
                    'model' => config('ai.model'),
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => config('ai.prompts.system'),
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildUserPrompt($contact),
                        ],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                "AI provider [{$provider}] connection failed: {$exception->getMessage()}",
                previous: $exception
            );
        } catch (RequestException $exception) {
            $status = $exception->response?->status() ?? 0;
            $body = mb_substr((string) $exception->response?->body(), 0, 500);

            throw new RuntimeException(
                "AI provider [{$provider}] error: HTTP {$status} — {$body}",
                previous: $exception
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "AI provider [{$provider}] unexpected error: {$exception->getMessage()}",
                previous: $exception
            );
        }

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'AI provider [%s] error: HTTP %s — %s',
                $provider,
                $response->status(),
                mb_substr($response->body(), 0, 500)
            ));
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException("AI provider [{$provider}] returned an empty response.");
        }

        // Some models wrap JSON in markdown fences — strip them defensively.
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
            $content = preg_replace('/\s*```$/', '', $content) ?? $content;
        }

        $payload = json_decode($content, true);

        if (! is_array($payload)) {
            throw new RuntimeException("AI provider [{$provider}] returned invalid JSON: ".mb_substr($content, 0, 200));
        }

        return AiAnalysisResult::fromArray($payload, usedFallback: false, provider: $provider);
    }

    private function buildUserPrompt(ContactData $contact): string
    {
        return implode("\n", [
            "Name: {$contact->name}",
            "Email: {$contact->email}",
            "Phone: {$contact->phone}",
            "Comment: {$contact->comment}",
        ]);
    }
}
