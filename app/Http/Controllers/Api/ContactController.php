<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Contact', description: 'Contact form API')]
class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService,
    ) {}

    #[OA\Post(
        path: '/api/contact',
        summary: 'Submit contact form',
        description: 'Validates input, analyzes message with AI, stores the request, sends emails to owner and user.',
        tags: ['Contact'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'phone', 'email', 'comment'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Иван Петров'),
                    new OA\Property(property: 'phone', type: 'string', example: '+7 999 123-45-67'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
                    new OA\Property(property: 'comment', type: 'string', example: 'Здравствуйте! Интересует сотрудничество по Laravel-проекту.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Contact processed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Contact request processed successfully.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 429, description: 'Rate limit exceeded'),
            new OA\Response(response: 502, description: 'Email delivery failed'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = ContactData::fromValidated(
            $request->validated(),
            $request->ip() ?? '0.0.0.0',
            (string) $request->userAgent()
        );

        $result = $this->contactService->handle($contact);

        return response()->json([
            'success' => true,
            'message' => 'Contact request processed successfully.',
            'data' => $result,
        ], 201);
    }
}
