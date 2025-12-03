<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyProfileRequest;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


class ThirdPartyProfileController extends Controller
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

        if (!$user->thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        /** @var ThirdParties $thirdParty */
        $thirdParty = $user->thirdParty;

        // The ThirdPartyResource expects user details. We attach them temporarily to the ThirdParties model.
        $thirdParty->setAttribute('FirstName', $user->FirstName);
        $thirdParty->setAttribute('LastName', $user->LastName);
        $thirdParty->setAttribute('Email', $user->Email);
        $thirdParty->setAttribute('Phone', $user->Phone);

        $thirdParty->load(['categories', 'country', 'types']);

        return response()->json([
            'user_profile' => new ThirdPartyResource($thirdParty),
        ]);
    }

    /**
     * Update the authenticated user's profile and associated ThirdParty record.
     *
     * @param UpdateThirdPartyProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateThirdPartyProfileRequest $request): JsonResponse
    {
        /** @var ThirdPartyUser $user */
        $user = Auth::guard('sanctum')->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        $data = $request->validated();
        $modifierId = $user->Id; // Use the User's primary ID for modification tracking

        try {
            DB::transaction(function () use ($user, $thirdParty, $data, $modifierId) {
                // 1. Update ThirdPartyUser details
                $userUpdateData = array_filter([
                    'FirstName' => $data['firstName'] ?? null,
                    'LastName' => $data['lastName'] ?? null,
                    'Phone' => $data['phone'] ?? null,
                    // If email changes, it's typically a separate flow involving re-verification, 
                    // but we update the ThirdPartyUser record as it's the auth model.
                    // 'Email' => $data['email'] ?? null,
                ], fn($value) => !is_null($value));

                if (!empty($userUpdateData)) {
                    $user->fill($userUpdateData)->save();
                }

                // 2. Update ThirdParties (Business) details
                $thirdPartyUpdateData = array_filter([
                    'TradingName' => $data['tradingName'] ?? null,
                    'BusinessType' => $data['businessType'] ?? null,
                    'RegistrationNumber' => $data['registrationNumber'] ?? null,
                    'TaxPIN' => $data['taxPin'] ?? null,
                    'VATNumber' => $data['vatNumber'] ?? null,
                    'CountryId' => $data['countryId'] ?? null,
                    'PhysicalAddress' => $data['physicalAddress'] ?? null,
                    'Website' => $data['website'] ?? null,
                ], fn($value) => !is_null($value));

                if (!empty($thirdPartyUpdateData)) {
                    $thirdPartyUpdateData['ModifiedBy'] = $modifierId;
                    $thirdPartyUpdateData['ModifiedOn'] = now();
                    $thirdParty->fill($thirdPartyUpdateData)->save();
                }

                // 3. Update Categories (M-to-M relationship)
                if (isset($data['categories']) && is_array($data['categories'])) {
                    $thirdParty->categories()->sync($data['categories']);
                }
            });
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), ['user_id' => $user->Id, 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }

        // Reload the models and attach user data for the response
        $user->refresh();
        $thirdParty->refresh()->load(['categories', 'country', 'types']);
        $thirdParty->setAttribute('FirstName', $user->FirstName);
        $thirdParty->setAttribute('LastName', $user->LastName);
        $thirdParty->setAttribute('Email', $user->Email);
        $thirdParty->setAttribute('Phone', $user->Phone);

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyResource($thirdParty),
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
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        $modifierId = $user->Id;

        try {
            $rules = [
                'firstName' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z\s\'-]+$/'],
                'lastName' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z\s\'-]+$/'],
                'phone' => ['sometimes', 'string', 'min:8', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
                'email' => ['sometimes', 'string', 'email', 'max:254', 'unique:t_ThirdPartyUsers,Email,' . $user->Id . ',Id'],
                'tradingName' => ['sometimes', 'string', 'max:255'],
                'businessType' => ['sometimes', 'string', 'max:255'],
                'registrationNumber' => ['sometimes', 'string', 'max:255'],
                'taxPin' => ['sometimes', 'string', 'max:255'],
                'vatNumber' => ['sometimes', 'string', 'max:255'],
                'countryId' => ['sometimes', 'integer'],
                'physicalAddress' => ['sometimes', 'string', 'max:255'],
                'website' => ['sometimes', 'nullable', 'string', 'url', 'max:255'],
                'categories' => ['sometimes', 'array'],
                'categories.*' => ['integer', 'exists:t_SupplierCategories,Id'],
            ];

            $validatedData = $request->validate($rules);

            if (empty($validatedData)) {
                return response()->json(['message' => __('auth.invalid_fields')], 400);
            }

            DB::transaction(function () use ($user, $thirdParty, $validatedData, $modifierId) {
                // 1. Update ThirdPartyUser details
                $userUpdateFields = array_filter([
                    'FirstName' => $validatedData['firstName'] ?? null,
                    'LastName' => $validatedData['lastName'] ?? null,
                    'Phone' => $validatedData['phone'] ?? null,
                    'Email' => $validatedData['email'] ?? null,
                ], fn($value) => !is_null($value));

                if (!empty($userUpdateFields)) {
                    $user->fill($userUpdateFields)->save();
                }

                // 2. Update ThirdParties (Business) details
                $thirdPartyUpdateFields = array_filter([
                    'TradingName' => $validatedData['tradingName'] ?? null,
                    'BusinessType' => $validatedData['businessType'] ?? null,
                    'RegistrationNumber' => $validatedData['registrationNumber'] ?? null,
                    'TaxPIN' => $validatedData['taxPin'] ?? null,
                    'VATNumber' => $validatedData['vatNumber'] ?? null,
                    'CountryId' => $validatedData['countryId'] ?? null,
                    'PhysicalAddress' => $validatedData['physicalAddress'] ?? null,
                    'Website' => $validatedData['website'] ?? null,
                ], fn($value) => !is_null($value));

                if (!empty($thirdPartyUpdateFields)) {
                    $thirdPartyUpdateFields['ModifiedBy'] = $modifierId;
                    $thirdPartyUpdateFields['ModifiedOn'] = now();
                    $thirdParty->fill($thirdPartyUpdateFields)->save();
                }

                // 3. Update Categories
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
            Log::error('Profile partial update failed: ' . $e->getMessage(), ['user_id' => $user->Id, 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }

        $user->refresh();
        $thirdParty->refresh()->load(['categories', 'country', 'types']);
        $thirdParty->setAttribute('FirstName', $user->FirstName);
        $thirdParty->setAttribute('LastName', $user->LastName);
        $thirdParty->setAttribute('Email', $user->Email);
        $thirdParty->setAttribute('Phone', $user->Phone);

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyResource($thirdParty),
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

            // Update password on ThirdPartyUser model
            $user->fill([
                'Password' => Hash::make($request->new_password),
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ])->save();

            return response()->json(['message' => __('auth.password_changed_ok')], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('auth.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Password change failed: ' . $e->getMessage(), ['user_id' => $user->Id, 'exception' => $e]);
            return response()->json(['message' => __('auth.password_change_failed')], 500);
        }
    }

    /**
     * Delete the authenticated user's profile and associated ThirdParty record.
     * This is typically a soft delete (marking inactive/deleted).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        /** @var ThirdPartyUser $user */
        $user = Auth::guard('sanctum')->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        try {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (!Hash::check($request->password, $user->Password)) {
                throw ValidationException::withMessages([
                    'password' => [__('auth.password_mismatch')],
                ]);
            }

            DB::transaction(function () use ($user, $thirdParty, $request) {
                // 1. Soft-delete/deactivate ThirdParty (Business)
                $thirdParty->IsActive = false;
                $thirdParty->DeletedBy = $user->Id;
                $thirdParty->save();
                $thirdParty->delete(); // Assuming this triggers a soft delete field (deleted_at)

                // 2. Soft-delete/deactivate ThirdPartyUser
                $user->IsActive = false;
                $user->DeletedBy = $user->Id;
                $user->save();
                $user->delete(); // Assuming this triggers a soft delete field (deleted_at)

                // 3. Revoke all tokens
                $request->user()->tokens()->delete();
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('auth.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Profile deletion failed: ' . $e->getMessage(), ['user_id' => $user->Id, 'exception' => $e]);
            return response()->json(['message' => __('auth.profile_delete_failed')], 500);
        }

        return response()->json(['message' => __('auth.profile_delete_ok')], 204);
    }
}
