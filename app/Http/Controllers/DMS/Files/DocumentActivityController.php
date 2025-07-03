<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\DMS\Document;
use App\Traits\Controller\ActivitiesTrait;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Exceptions\InvalidConfiguration;

class DocumentActivityController extends Controller
{
    use ActivitiesTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        try {
            return $this->activities($document->userActivities(), ['causer']);
        } catch (InvalidConfiguration $e) {
            return $this->errored('invalid configuration');
        }
    }
}
