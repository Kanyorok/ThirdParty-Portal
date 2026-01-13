<?php 

namespace App\Http\Controllers\Procurement\Prequalification\Api;

use App\Http\Controllers\Controller;
use App\Services\Procurement\API\Prequalification\PrequalificationService;
use App\Http\Resources\Procurement\API\OpenRoundResource;
use Illuminate\Support\Facades\Auth;
use App\Traits\Model\ApiResponseTrait;

class PrequalificationRoundController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected PrequalificationService $service
    ) {}

    public function index()
    {
        try {
            $rounds = $this->service->getOpenRounds();
            if ($rounds->isEmpty()) {
                return $this->successResponse([], 'No open rounds found at the moment.', 200);
            }
            return $this->successResponse(
                OpenRoundResource::collection($rounds),
                'Open rounds retrieved successfully.'
            );
        } catch (Exception $e){
            return $this->errorResponse(
                'Failed to retrieve open rounds.', 
                500, 
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }
}