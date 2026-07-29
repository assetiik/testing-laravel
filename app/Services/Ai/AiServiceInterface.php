<?php

namespace App\Services\Ai;

use App\DTOs\AiAnalysisResult;
use App\DTOs\ContactData;

interface AiServiceInterface
{
    public function analyze(ContactData $contact): AiAnalysisResult;
}
