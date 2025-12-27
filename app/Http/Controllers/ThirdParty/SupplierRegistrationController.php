<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use App\Http\Requests\ThirdParty\RegisterThirdPartyDetailsRequest;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Throwable;

class SupplierRegistrationController extends Controller
{
    public function __construct(protected RegistrationService $registrationService) {}

    public function __invoke(RegisterThirdPartyUserRequest $request): JsonResponse
    {
        try {
            $user = $this->registrationService->createInitialAccount($request->validated());
            $token = $user->createToken('auth_token', ['thirdparty'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration started successfully.',
                'token'   => $token,
                'data'    => new ThirdPartyUserResource($user)
            ], 201);
        } catch (QueryException $e) {
            Log::error("Step 1 SQL Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database Error: ' . $e->getMessage(),
                'details' => $e->errorInfo
            ], 500);
        } catch (Throwable $e) {
            Log::error("Registration Step 1 Failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function completeProfile(RegisterThirdPartyDetailsRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $this->registrationService->registerThirdPartyDetails($user, $request->validated());

            $user->load(['thirdParty.country']);

            return response()->json([
                'success' => true,
                'message' => 'Organization profile initialized successfully.',
                'data'    => new ThirdPartyUserResource($user)
            ], 200);
        } catch (QueryException $e) {
            Log::error("Step 2 SQL Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'SQL Server Error: ' . $e->getMessage(),
                'sql' => $e->getSql()
            ], 500);
        } catch (Throwable $e) {
            Log::error("Registration Step 2 Failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
