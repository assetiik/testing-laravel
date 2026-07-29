<?php

namespace App\Services\Ai;

use App\DTOs\AiAnalysisResult;
use App\DTOs\ContactData;

class FallbackAiService implements AiServiceInterface
{
    public function analyze(ContactData $contact): AiAnalysisResult
    {
        $text = mb_strtolower($contact->comment);
        $sentiment = $this->detectSentiment($text);
        $category = $this->detectCategory($text);
        $priority = $this->detectPriority($category, $sentiment);

        return new AiAnalysisResult(
            sentiment: $sentiment,
            sentimentScore: match ($sentiment) {
                'positive' => 0.55,
                'negative' => -0.55,
                default => 0.0,
            },
            category: $category,
            priority: $priority,
            summary: mb_substr($contact->comment, 0, 160),
            suggestedReply: $this->buildReply($contact, $category),
            usedFallback: true,
            provider: 'fallback',
        );
    }

    private function detectSentiment(string $text): string
    {
        $positive = ['спасибо', 'отлично', 'интерес', 'класс', 'great', 'thanks', 'love', 'awesome'];
        $negative = ['плохо', 'ужасно', 'недоволен', 'complaint', 'bad', 'awful', 'hate'];

        $positiveHits = $this->countHits($text, $positive);
        $negativeHits = $this->countHits($text, $negative);

        if ($positiveHits > $negativeHits) {
            return 'positive';
        }

        if ($negativeHits > $positiveHits) {
            return 'negative';
        }

        return 'neutral';
    }

    private function detectCategory(string $text): string
    {
        $map = [
            'job_offer' => ['вакансия', 'работа', 'оффер', 'job', 'hire', 'position', 'salary'],
            'collaboration' => ['сотрудничество', 'партнер', 'collaboration', 'partner', 'together'],
            'question' => ['вопрос', 'как', 'можно ли', 'question', 'how', 'what'],
            'feedback' => ['отзыв', 'feedback', 'review', 'впечатление'],
            'spam' => ['crypto', 'casino', 'viagra', 'bitcoin free', 'click here'],
        ];

        foreach ($map as $category => $keywords) {
            if ($this->countHits($text, $keywords) > 0) {
                return $category;
            }
        }

        return 'other';
    }

    private function detectPriority(string $category, string $sentiment): string
    {
        if ($category === 'job_offer' || $category === 'collaboration') {
            return 'high';
        }

        if ($category === 'spam' || $sentiment === 'negative') {
            return 'low';
        }

        return 'medium';
    }

    private function buildReply(ContactData $contact, string $category): string
    {
        $name = $contact->name;

        return match ($category) {
            'job_offer' => "Здравствуйте, {$name}! Спасибо за интерес к моему профилю. Я получил ваше предложение и свяжусь с вами в ближайшее время.",
            'collaboration' => "Здравствуйте, {$name}! Благодарю за предложение о сотрудничестве. Я изучил ваше сообщение и отвечу в ближайшие дни.",
            'question' => "Здравствуйте, {$name}! Спасибо за вопрос. Я уже получил обращение и скоро вернусь с ответом.",
            'feedback' => "Здравствуйте, {$name}! Спасибо за обратную связь — она очень важна для меня.",
            default => "Здравствуйте, {$name}! Спасибо за обращение. Я получил ваше сообщение и отвечу как можно скорее.",
        };
    }

    /**
     * @param  list<string>  $keywords
     */
    private function countHits(string $text, array $keywords): int
    {
        $hits = 0;

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $hits++;
            }
        }

        return $hits;
    }
}
