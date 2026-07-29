<x-mail::message>
# Новое обращение

**Имя:** {{ $contact->name }}<br>
**Email:** {{ $contact->email }}<br>
**Телефон:** {{ $contact->phone }}

## Комментарий
{{ $contact->comment }}

## AI suggested reply
{{ $suggestedReply }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
