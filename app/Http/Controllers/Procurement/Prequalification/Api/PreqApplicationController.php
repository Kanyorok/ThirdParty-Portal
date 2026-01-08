<?php 

namespace App\Http\Controllers\Procurement\Prequalification\Api;

use App\Http\Controllers\Controller;
use App\Services\Procurement\API\Prequalification\PrequalificationService;
use App\Http\Requests\Procurement\prequalification\StoreApplicationRequest;
use App\Http\Resources\Procurement\API\ApplicationResource;
use App\Traits\Model\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PreqApplicationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected PrequalificationService $service
    ) {}

    public function initialize(int $roundId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->thirdParty || !$user->thirdParty->supplierMaster) {
                return $this->errorResponse('Supplier profile not found. Please complete your registration.', 422);
            }

            $application = $this->service->initializeApplication($roundId);
            return $this->successResponse(new ApplicationResource($application), 'Initialized');
        } catch (Exception $e) {
            return $this->errorResponse('Initialization failed', 500, $e->getMessage());
        }
    }

    public function store(StoreApplicationRequest $request, int $applicationId): JsonResponse
    {
        try {
            $application = $this->service->updateApplication($applicationId, $request->validated());
            return $this->successResponse(new ApplicationResource($application), 'Saved');
        } catch (Exception $e) {
            return $this->errorResponse('Save failed', 500, $e->getMessage());
        }
    }

    public function submit(int $applicationId): JsonResponse
    {
        try {
            $application = $this->service->submitApplication($applicationId);
            return $this->successResponse(new ApplicationResource($application), 'Submitted');
        } catch (Exception $e) {
            return $this->errorResponse('Submission failed', 422, $e->getMessage());
        }
    }
}