<?php

namespace App\Mail;

use App\DTOs\ContactData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ContactData $contact,
        public readonly string $suggestedReply,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новое обращение с лендинга: '.$this->contact->name,
            replyTo: [$this->contact->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.owner',
            with: [
                'contact' => $this->contact,
                'suggestedReply' => $this->suggestedReply,
            ],
        );
    }
}
