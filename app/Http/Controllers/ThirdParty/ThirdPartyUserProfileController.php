<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\UpdateThirdPartyUserProfileRequest;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ThirdPartyUserProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        $user->load('thirdParty');

        return response()->json([
            'user_profile' => new ThirdPartyUserResource($user),
            'third_party_entity' => new ThirdPartyResource($user->thirdParty),
        ]);
    }

    public function update(UpdateThirdPartyUserProfileRequest $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.3rd_party_not_found')], 404);
        }

        $data = $request->validated();

        DB::transaction(function () use ($user, $thirdParty, $data) {
            $user->fill([
                'FirstName' => $data['firstName'] ?? $user->FirstName,
                'LastName' => $data['lastName'] ?? $user->LastName,
                'Phone' => $data['phone'] ?? $user->Phone,
                'Gender' => $data['gender'] ?? $user->Gender,
                'ImageId' => $data['imageId'] ?? $user->ImageId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ])->save();

            $thirdParty->fill([
                'TradingName' => $data['tradingName'] ?? $thirdParty->TradingName,
                'BusinessType' => $data['businessType'] ?? $thirdParty->BusinessType,
                'RegistrationNumber' => $data['registrationNumber'] ?? $thirdParty->RegistrationNumber,
                'TaxPIN' => $data['taxPin'] ?? $thirdParty->TaxPIN,
                'VATNumber' => $data['vatNumber'] ?? $thirdParty->VATNumber,
                'Country' => $data['country'] ?? $thirdParty->Country,
                'PhysicalAddress' => $data['physicalAddress'] ?? $thirdParty->PhysicalAddress,
                'Website' => $data['website'] ?? $thirdParty->Website,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ])->save();
        });

        $user->load('thirdParty');

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyUserResource($user),
            'third_party_entity' => new ThirdPartyResource($user->thirdParty),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        $user->IsActive = false;
        $user->DeletedBy = Auth::id();
        $user->save();

        $user->delete();

        $request->user()->tokens()->delete();

        return response()->json(['message' => __('auth.profile_delete_ok')], 204);
    }
}
