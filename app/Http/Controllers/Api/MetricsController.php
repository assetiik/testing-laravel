<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ContactRepository;
use App\Repositories\MetricsRepository;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MetricsController extends Controller
{
    public function __construct(
        private readonly MetricsRepository $metricsRepository,
        private readonly ContactRepository $contactRepository,
    ) {}

    #[OA\Get(
        path: '/api/metrics',
        summary: 'Contact metrics',
        description: 'Returns aggregated contact statistics stored in a JSON file.',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Metrics retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $metrics = $this->metricsRepository->get();
        $metrics['stored_contacts'] = $this->contactRepository->count();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }
}
