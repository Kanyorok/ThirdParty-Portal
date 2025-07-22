<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\UpdateThirdPartyUserProfileRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyUserProfileRequest as ThirdPartyAuthUpdateThirdPartyUserProfileRequest;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThirdPartyUserProfileController extends Controller
{
    /**
     * Display the authenticated ThirdPartyUser's profile and associated ThirdParty entity.
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        // Load thirdParty relationship
        $user->load('thirdParty');

        return response()->json([
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

    /**
     * Update the authenticated ThirdPartyUser's profile and the associated ThirdParty entity.
     *
     * @param UpdateThirdPartyUserProfileRequest $request
     * @return JsonResponse
     */
    public function update(ThirdPartyAuthUpdateThirdPartyUserProfileRequest $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.3rd_party_not_found')], 404);
        }

        $data = $request->validated();

        try {
            DB::transaction(function () use ($user, $thirdParty, $data) {
                // Update basic user information
                $user->fill([
                    'FirstName' => $data['firstName'] ?? $user->FirstName,
                    'LastName' => $data['lastName'] ?? $user->LastName,
                    'Phone' => $data['phone'] ?? $user->Phone,
                    'Gender' => $data['gender'] ?? $user->Gender,
                    'ImageId' => $data['imageId'] ?? $user->ImageId,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ])->save();

                // Update associated third-party entity information
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
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }

        $user->load('thirdParty');

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

    /**
     * Soft delete and deactivate the authenticated ThirdPartyUser's account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        try {
            DB::transaction(function () use ($user, $request) {
                // Mark user as inactive and set DeletedBy before soft deleting
                $user->IsActive = false;
                $user->DeletedBy = Auth::id();
                $user->save(); // To persist IsActive and DeletedBy changes

                $user->delete(); // SoftDelete
                // Revoke user's current tokens for immediate logout
                $request->user()->tokens()->delete();
            });
        } catch (\Exception $e) {
            Log::error('Profile deletion failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_delete_failed')], 500);
        }

        return response()->json(['message' => __('auth.profile_delete_ok')], 204);
    }
}
