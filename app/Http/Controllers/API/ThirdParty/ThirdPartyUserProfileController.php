<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyUserProfileRequest as ThirdPartyAuthUpdateThirdPartyUserProfileRequest;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


class ThirdPartyUserProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
    /** @var ThirdPartyUser $user */
    $user = Auth::guard('sanctum')->user();
        $user->load('thirdParty.categories');

        return response()->json([
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

    /**
     * Update the authenticated user's profile and associated ThirdParty record.
     *
     * @param ThirdPartyAuthUpdateThirdPartyUserProfileRequest $request
     * @return JsonResponse
     */
    public function update(ThirdPartyAuthUpdateThirdPartyUserProfileRequest $request): JsonResponse
    {
    /** @var ThirdPartyUser $user */
    $user = Auth::guard('sanctum')->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.3rd_party_not_found')], 404);
        }

        $data = $request->validated();

        try {
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
                    'CountryId' => $data['countryId'] ?? $thirdParty->CountryId,
                    'PhysicalAddress' => $data['physicalAddress'] ?? $thirdParty->PhysicalAddress,
                    'Website' => $data['website'] ?? $thirdParty->Website,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ])->save();

                // Check for categories and sync them
                if (isset($data['categories']) && is_array($data['categories'])) {
                    $thirdParty->categories()->sync($data['categories']);
                }
            });
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }

        // Load the ThirdParty profile AND its nested categories for the response
        $user->load('thirdParty.categories');

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

    /**
     * Perform a partial update on the authenticated user's profile and associated ThirdParty record.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function partialUpdate(Request $request): JsonResponse
    {
    /** @var ThirdPartyUser $user */
    $user = Auth::guard('sanctum')->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.3rd_party_not_found')], 404);
        }

        try {
            $rules = [
                'firstName' => ['sometimes', 'string', 'max:50', 'regex:/^[a-zA-Z\s\'-]+$/'],
                'lastName' => ['sometimes', 'string', 'max:50', 'regex:/^[a-zA-Z\s\'-]+$/'],
                // E.164 phone format
                'phone' => ['sometimes', 'string', 'min:8', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'email' => ['sometimes', 'string', 'email', 'max:254', 'unique:t_ThirdPartyUsers,email,' . $user->id . ',id'],
                'gender' => ['sometimes', 'nullable', 'string', 'max:20'],
                'imageId' => ['sometimes', 'nullable', 'integer'],
                'tradingName' => ['sometimes', 'string', 'max:255'],
                'businessType' => ['sometimes', 'string', 'max:255'],
                'registrationNumber' => ['sometimes', 'string', 'max:255'],
                'taxPin' => ['sometimes', 'string', 'max:255'],
                'vatNumber' => ['sometimes', 'string', 'max:255'],
                'country' => ['sometimes', 'string', 'max:255'],
                'physicalAddress' => ['sometimes', 'string', 'max:255'],
                'website' => ['sometimes', 'nullable', 'string', 'url', 'max:255'],
                'categories' => ['sometimes', 'array'],
                'categories.*' => ['integer', 'exists:t_SupplierCategories,Id'],
            ];

            $validatedData = $request->validate($rules);

            if (empty($validatedData)) {
                return response()->json(['message' => __('auth.invalid_fields')], 400);
            }

            DB::transaction(function () use ($user, $thirdParty, $validatedData) {
                $userUpdateFields = [];
                $thirdPartyUpdateFields = [];

                // Assign user-related fields
                $userUpdateFields = array_filter([
                    'FirstName' => $validatedData['firstName'] ?? null,
                    'LastName' => $validatedData['lastName'] ?? null,
                    'Phone' => $validatedData['phone'] ?? null,
                    'Email' => $validatedData['email'] ?? null,
                    'Gender' => $validatedData['gender'] ?? null,
                    'ImageId' => $validatedData['imageId'] ?? null,
                ]);

                if (!empty($userUpdateFields)) {
                    $userUpdateFields['ModifiedBy'] = Auth::id();
                    $userUpdateFields['ModifiedOn'] = now();
                    $user->fill($userUpdateFields)->save();
                }

                // Assign ThirdParty-related fields
                $thirdPartyUpdateFields = array_filter([
                    'TradingName' => $validatedData['tradingName'] ?? null,
                    'BusinessType' => $validatedData['businessType'] ?? null,
                    'RegistrationNumber' => $validatedData['registrationNumber'] ?? null,
                    'TaxPIN' => $validatedData['taxPin'] ?? null,
                    'VATNumber' => $validatedData['vatNumber'] ?? null,
                    'Country' => $validatedData['country'] ?? null,
                    'CountryId' => $validatedData['countryId'] ?? null,
                    'PhysicalAddress' => $validatedData['physicalAddress'] ?? null,
                    'Website' => $validatedData['website'] ?? null,
                ]);

                if (!empty($thirdPartyUpdateFields)) {
                    $thirdPartyUpdateFields['ModifiedBy'] = Auth::id();
                    $thirdPartyUpdateFields['ModifiedOn'] = now();
                    $thirdParty->fill($thirdPartyUpdateFields)->save();
                }

                // Check for categories and sync them
                if (isset($validatedData['categories'])) {
                    $thirdParty->categories()->sync($validatedData['categories']);
                }
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('auth.invalid_fields'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Profile partial update failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }

        // Load the ThirdParty profile AND its nested categories for the response
        $user->load('thirdParty.categories');

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

    /**
     * Change the authenticated user's password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
    /** @var ThirdPartyUser $user */
    $user = Auth::guard('sanctum')->user();

        try {
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (!Hash::check($request->current_password, $user->Password)) {
                throw ValidationException::withMessages([
                    'current_password' => [__('auth.password_mismatch')],
                ]);
            }

            $user->fill([
                'Password' => Hash::make($request->new_password),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ])->save();

            return response()->json(['message' => __('auth.password_changed_ok')], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('auth.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Password change failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
            return response()->json(['message' => __('auth.password_change_failed')], 500);
        }
    }

    /**
     * Delete the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
    /** @var ThirdPartyUser $user */
    $user = Auth::guard('sanctum')->user();

        try {
            DB::transaction(function () use ($user, $request) {
                $user->IsActive = false;
                $user->DeletedBy = Auth::id();
                $user->save();

                $user->delete();
                $request->user()->tokens()->delete();
            });
        } catch (\Exception $e) {
            Log::error('Profile deletion failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_delete_failed')], 500);
        }

        return response()->json(['message' => __('auth.profile_delete_ok')], 204);
    }
}
