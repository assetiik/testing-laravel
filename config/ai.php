<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | The primary provider is called over the OpenAI-compatible
    | "chat/completions" protocol, so switching between OpenAI, Groq,
    | OpenRouter or a local model only requires changing the env values below.
    |
    | If the provider is unavailable or misconfigured, FallbackAiService
    | takes over and the API keeps working.
    |
    */
    'provider' => env('AI_PROVIDER', 'groq'),

    'enabled' => (bool) env('AI_ENABLED', true),

    'timeout' => (int) env('AI_TIMEOUT', 15),

    'api_key' => env('AI_API_KEY'),

    'base_url' => env('AI_BASE_URL', 'https://api.groq.com/openai/v1'),

    'model' => env('AI_MODEL', 'llama-3.3-70b-versatile'),

    /*
    |--------------------------------------------------------------------------
    | Prompts
    |--------------------------------------------------------------------------
    */
    'prompts' => [
        'system' => <<<'PROMPT'
You are an assistant for a developer portfolio contact form.
Analyze the user's message and return ONLY valid JSON with this schema:
{
  "sentiment": "positive|neutral|negative",
  "sentiment_score": 0.0,
  "category": "job_offer|collaboration|question|feedback|spam|other",
  "priority": "low|medium|high",
  "summary": "short summary in the same language as the comment",
  "suggested_reply": "polite professional reply in the same language as the comment"
}
Rules:
- sentiment_score is from -1.0 (very negative) to 1.0 (very positive)
- Keep suggested_reply concise (2-4 sentences)
- Do not invent facts about the developer
- Never include markdown or code fences
PROMPT,
    ],

];
