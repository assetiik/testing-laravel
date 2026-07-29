<?php

namespace App\Services;

use App\DTOs\ContactData;
use App\Exceptions\RateLimitExceededException;
use App\Mail\ContactOwnerMail;
use App\Mail\ContactUserMail;
use App\Repositories\ContactRepository;
use App\Repositories\MetricsRepository;
use App\Repositories\RateLimitRepository;
use App\Services\Ai\AiServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactService
{
    public function __construct(
        private readonly AiServiceInterface $aiService,
        private readonly ContactRepository $contactRepository,
        private readonly MetricsRepository $metricsRepository,
        private readonly RateLimitRepository $rateLimitRepository,
    ) {}

    /**
     * Full contact flow: rate-limit → AI → persist → metrics → emails.
     *
     * @return array<string, mixed>
     */
    public function handle(ContactData $contact): array
    {
        $rateLimitKey = $this->rateLimitKey($contact);

        if ($this->rateLimitRepository->tooManyAttempts($rateLimitKey)) {
            throw new RateLimitExceededException(
                $this->rateLimitRepository->retryAfter($rateLimitKey)
            );
        }

        $analysis = $this->aiService->analyze($contact);
        $record = $this->contactRepository->store($contact, $analysis);
        $this->metricsRepository->increment($analysis);
        $this->rateLimitRepository->hit($rateLimitKey);

        $emailsDelivered = $this->sendEmails($contact, $analysis->suggestedReply);

        Log::channel('api')->info('Contact processed successfully', [
            'id' => $record['id'],
            'email' => $contact->email,
            'category' => $analysis->category,
            'sentiment' => $analysis->sentiment,
            'ai_fallback' => $analysis->usedFallback,
            'emails_delivered' => $emailsDelivered,
        ]);

        return [
            'id' => $record['id'],
            'created_at' => $record['created_at'],
            'ai' => $analysis->toArray(),
            'emails_delivered' => $emailsDelivered,
            'rate_limit' => [
                'remaining' => $this->rateLimitRepository->remaining($rateLimitKey),
                'retry_after' => $this->rateLimitRepository->retryAfter($rateLimitKey),
            ],
        ];
    }

    private function sendEmails(ContactData $contact, string $suggestedReply): bool
    {
        try {
            Mail::to(config('contact.owner_email'))
                ->send(new ContactOwnerMail($contact, $suggestedReply));

            Mail::to($contact->email)
                ->send(new ContactUserMail($contact, $suggestedReply));

            return true;
        } catch (Throwable $exception) {
            Log::channel('api')->error('Failed to send contact emails', [
                'error' => $exception->getMessage(),
                'email' => $contact->email,
            ]);

            return false;
        }
    }

    private function rateLimitKey(ContactData $contact): string
    {
        return 'contact:'.$contact->ip;
    }
}
