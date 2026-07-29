<x-mail::message>
# Здравствуйте, {{ $contact->name }}!

Спасибо за обращение. Мы получили ваше сообщение и ответим в ближайшее время.

## Ваше сообщение
{{ $contact->comment }}

## Предварительный ответ
{{ $suggestedReply }}

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
