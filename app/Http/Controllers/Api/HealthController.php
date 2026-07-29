<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'System', description: 'Health and metrics')]
class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Health check',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service is healthy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'checks', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $storageWritable = File::isWritable(storage_path('app/private'));
        $logsWritable = File::isWritable(storage_path('logs'));
        $aiConfigured = filled(config('ai.api_key')) && config('ai.enabled');

        $healthy = $storageWritable && $logsWritable;

        return response()->json([
            'success' => $healthy,
            'status' => $healthy ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => [
                'storage_writable' => $storageWritable,
                'logs_writable' => $logsWritable,
                'ai_configured' => $aiConfigured,
                'ai_enabled' => (bool) config('ai.enabled'),
                'ai_provider' => config('ai.provider'),
                'ai_model' => config('ai.model'),
                'mailer' => config('mail.default'),
            ],
        ], $healthy ? 200 : 503);
    }
}
