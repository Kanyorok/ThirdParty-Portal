<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\CreateThirdPartyProfileRequest;
use App\Services\ThirdParties\ThirdPartyPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ThirdPartyPortalService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('third_party');

        if (!$user->hasProfile()) {
            return response()->json([
                'message' => 'No profile found.',
                'has_profile' => false,
            ], 404);
        }

        $thirdParty = $user->thirdParty->load(['types', 'country']);

        return response()->json([
            'data' => [
                'id' => $thirdParty->Id,
                'name' => $thirdParty->ThirdPartyName,
                'trading_name' => $thirdParty->TradingName,
                'email' => $thirdParty->Email,
                'phone' => $thirdParty->Phone,
                'physical_address' => $thirdParty->PhysicalAddress,
                'website' => $thirdParty->Website,
                'registration_number' => $thirdParty->RegistrationNumber,
                'tax_pin' => $thirdParty->TaxPIN,
                'country' => $thirdParty->country?->CountryName,
                'approval_status' => $thirdParty->ApprovalStatus?->value,
                'types' => $thirdParty->types->pluck('Code')->toArray(),
                'is_supplier' => $user->isSupplier(),
                'is_tenant' => $user->isTenant(),
                'is_customer' => $user->isCustomer(),
            ],
        ]);
    }

    public function store(CreateThirdPartyProfileRequest $request): JsonResponse
    {
        $user = $request->user('third_party');

        if ($user->hasProfile()) {
            return response()->json([
                'message' => 'Profile already exists.',
            ], 409);
        }

        $thirdParty = $this->service->createProfile($request, $user);

        return response()->json([
            'message' => $request->requiresApproval()
                ? 'Profile submitted for approval.'
                : 'Profile created successfully.',
            'data' => [
                'id' => $thirdParty->Id,
                'name' => $thirdParty->ThirdPartyName,
                'approval_status' => $thirdParty->ApprovalStatus->value,
                'types' => $thirdParty->types->pluck('Code')->toArray(),
            ],
        ], 201);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user('third_party');

        return response()->json([
            'data' => $this->service->getProfileStatus($user),
        ]);
    }
}
