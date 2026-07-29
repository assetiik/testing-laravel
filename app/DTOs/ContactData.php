<?php

namespace App\DTOs;

use App\Rules\PhoneNumber;

readonly class ContactData
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $email,
        public string $comment,
        public string $ip,
        public string $userAgent,
    ) {}

    /**
     * @param  array{name: string, phone: string, email: string, comment: string}  $validated
     */
    public static function fromValidated(array $validated, string $ip, string $userAgent): self
    {
        return new self(
            name: trim(strip_tags($validated['name'])),
            phone: PhoneNumber::normalize(strip_tags($validated['phone'])),
            email: strtolower(trim($validated['email'])),
            comment: trim(strip_tags($validated['comment'])),
            ip: $ip,
            userAgent: $userAgent,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'comment' => $this->comment,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
        ];
    }
}
