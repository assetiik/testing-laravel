<?php

namespace App\Console\Commands;

use App\DTOs\ContactData;
use App\Mail\ContactOwnerMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email? : Recipient, defaults to CONTACT_OWNER_EMAIL}';

    protected $description = 'Send a sample contact notification through the configured mailer';

    public function handle(): int
    {
        $recipient = $this->argument('email') ?? config('contact.owner_email');

        $this->line('Mailer: '.config('mail.default'));
        $this->line('From:   '.config('mail.from.address'));
        $this->line('To:     '.$recipient);

        $contact = new ContactData(
            name: 'Test User',
            phone: '+79991234567',
            email: 'test@example.com',
            comment: 'Проверка доставки письма через настроенный mailer.',
            ip: '127.0.0.1',
            userAgent: 'artisan',
        );

        try {
            Mail::to($recipient)->send(new ContactOwnerMail($contact, 'Спасибо за обращение!'));
        } catch (Throwable $exception) {
            $this->error('Delivery failed: '.$exception->getMessage());
            $this->line('The contact API keeps returning 201 with emails_delivered=false in this case.');

            return self::FAILURE;
        }

        $this->info('Sent.');

        return self::SUCCESS;
    }
}
