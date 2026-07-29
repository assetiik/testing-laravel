<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Developer Landing Contact API',
    description: 'Backend API for a developer portfolio contact form with AI analysis, email notifications, rate limiting and file-based metrics.'
)]
#[OA\Server(url: '/', description: 'Current host')]
#[OA\ExternalDocumentation(
    description: 'Project README',
    url: 'https://github.com/'
)]
class OpenApiSpec
{
}
