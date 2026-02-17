<?php

namespace App\Http\Controllers\DMS\LegalHold;

use App\Http\Controllers\Controller;
use App\Models\DMS\LegalHold;
use App\Traits\Controller\ActivitiesTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Exceptions\InvalidConfiguration;

class LegalHoldActivityController extends Controller
{
    use ActivitiesTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $legalHoldId): JsonResponse
    {
        $legalHold = LegalHold::query()->where('t_DMSLegalHolds.Ref', $legalHoldId)->first();
        if (! $legalHold instanceof LegalHold) {
            return $this->errored('Invalid legal hold provided');
        }
        $this->authorize('view', $legalHold);

        try {
            return $this->activities($legalHold->userActivities(), ['causer']);
        } catch (InvalidConfiguration $e) {
            return $this->errored('invalid configuration');
        }
    }
}
