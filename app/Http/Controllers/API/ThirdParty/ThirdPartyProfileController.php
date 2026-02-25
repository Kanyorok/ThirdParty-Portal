<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyProfileRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyResource;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Hash, Log};
use Illuminate\Validation\{Rule, ValidationException};

class ThirdPartyProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = Auth::user()->load(['thirdParty.categories', 'thirdParty.country', 'thirdParty.types']);

        if (! $user->thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        return response()->json([
            'user_profile' => new ThirdPartyResource($user->thirdParty),
        ]);
    }

    public function update(UpdateThirdPartyProfileRequest $request): JsonResponse
    {
        $user = Auth::user();
        $thirdParty = $user->thirdParty;

        if (! $thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        try {
            DB::transaction(function () use ($user, $thirdParty, $request) {
                $data = $request->validated();

                $user->update(array_filter([
                    'FirstName' => $data['firstName'] ?? null,
                    'LastName' => $data['lastName'] ?? null,
                    'Phone' => $data['phone'] ?? null,
                ]));

                $thirdParty->update(array_filter([
                    'TradingName' => $data['tradingName'] ?? null,
                    'BusinessType' => $data['businessType'] ?? null,
                    'RegistrationNumber' => $data['registrationNumber'] ?? null,
                    'TaxPIN' => $data['taxPin'] ?? null,
                    'VATNumber' => $data['vatNumber'] ?? null,
                    'CountryId' => $data['countryId'] ?? null,
                    'PhysicalAddress' => $data['physicalAddress'] ?? null,
                    'Website' => $data['website'] ?? null,
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]));

                if (isset($data['categories'])) {
                    $thirdParty->categories()->sync($data['categories']);
                }
            });

            return response()->json([
                'message' => __('auth.profile_update_ok'),
                'user_profile' => new ThirdPartyResource($thirdParty->refresh()->load(['categories', 'country', 'types'])),
            ]);
        } catch (\Exception $e) {
            Log::error("Profile Update Failed [User: {$user->Id}]: " . $e->getMessage());

            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }
    }

    public function partialUpdate(Request $request): JsonResponse
    {
        $user = Auth::user();
        $thirdParty = $user->thirdParty;

        if (! $thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        try {
            $validated = $request->validate([
                'firstName' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\\s\\\'-]+$/'],
                'lastName' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\\s\\\'-]+$/'],
                'phone' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^\\+[1-9]\\d{7,14}$/'],
                'gender' => ['sometimes', 'nullable'],
                'imageId' => ['sometimes', 'nullable', 'integer', 'exists:t_Images,Id'],
                'categories' => ['sometimes', 'array'],
                'categories.*' => ['integer', 'exists:t_SupplierCategories,Id'],
                'tradingName' => ['sometimes', 'nullable', 'string', 'max:255'],
                'businessType' => ['sometimes', 'nullable', 'string', 'max:100'],
                'registrationNumber' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('t_ThirdParties', 'RegistrationNumber')->ignore($thirdParty->Id, 'Id'),
                ],
                'taxPin' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('t_ThirdParties', 'TaxPIN')->ignore($thirdParty->Id, 'Id'),
                ],
                'vatNumber' => ['sometimes', 'nullable', 'string', 'max:50'],
                'countryId' => ['sometimes', 'nullable', 'integer', 'exists:t_Countries,Id'],
                'physicalAddress' => ['sometimes', 'nullable', 'string', 'max:500'],
                'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            ]);

            if (empty($validated)) {
                return response()->json(['message' => __('auth.invalid_fields')], 400);
            }

            DB::transaction(function () use ($validated, $user, $thirdParty) {
                $userUpdates = array_filter([
                    'FirstName' => $validated['firstName'] ?? null,
                    'LastName' => $validated['lastName'] ?? null,
                    'Phone' => $validated['phone'] ?? null,
                    'Gender' => $validated['gender'] ?? null,
                    'ImageId' => $validated['imageId'] ?? null,
                ], static fn ($value) => ! is_null($value));

                if (! empty($userUpdates)) {
                    $userUpdates['ModifiedBy'] = $user->Id;
                    $userUpdates['ModifiedOn'] = now();
                    $user->update($userUpdates);
                }

                $thirdPartyUpdates = array_filter([
                    'TradingName' => $validated['tradingName'] ?? null,
                    'BusinessType' => $validated['businessType'] ?? null,
                    'RegistrationNumber' => $validated['registrationNumber'] ?? null,
                    'TaxPIN' => $validated['taxPin'] ?? null,
                    'VATNumber' => $validated['vatNumber'] ?? null,
                    'CountryId' => $validated['countryId'] ?? null,
                    'PhysicalAddress' => $validated['physicalAddress'] ?? null,
                    'Website' => $validated['website'] ?? null,
                ], static fn ($value) => ! is_null($value));

                if (! empty($thirdPartyUpdates)) {
                    $thirdPartyUpdates['ModifiedBy'] = $user->Id;
                    $thirdPartyUpdates['ModifiedOn'] = now();
                    $thirdParty->update($thirdPartyUpdates);
                }

                if (isset($validated['categories'])) {
                    $thirdParty->categories()->sync($validated['categories']);
                }
            });

            return response()->json([
                'message' => __('auth.profile_update_ok'),
                'user_profile' => new ThirdPartyResource($thirdParty->refresh()->load(['categories', 'country', 'types'])),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('auth.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Profile Partial Update Failed [User: {$user->Id}]: " . $e->getMessage());

            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $user->Password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth.password_mismatch')],
            ]);
        }

        if (Hash::check($request->new_password, $user->Password)) {
            throw ValidationException::withMessages([
                'new_password' => [__('auth.password_reuse_not_allowed')],
            ]);
        }

        $user->update([
            'Password' => Hash::make($request->new_password),
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => now(),
        ]);

        return response()->json(['message' => __('auth.password_changed_ok')]);
    }

    public function toggleRole(Request $request): JsonResponse
    {
        $user = Auth::user();
        $thirdParty = $user->thirdParty;

        $request->validate([
            'roleId' => 'required|integer',
            'enable' => 'required|boolean',
        ]);

        $roleId = $request->roleId;
        $enable = $request->enable;

        try {
            DB::table('t_ThirdPartyType_ThirdParties')
                ->where('Id', $roleId)
                ->where('ThirdPartyId', $thirdParty->Id)
                ->update([
                    'DeletedOn' => $enable ? null : now(),
                    'DeletedBy' => $enable ? null : $user->Id,
                    'ModifiedOn' => now(),
                    'ModifiedBy' => $user->Id,
                ]);

            return response()->json([
                'message' => $enable ? 'Role authorized.' : 'Role access revoked.',
                'roles' => $thirdParty->refresh()->load('types')->types->map(fn ($t) => [
                    'id' => $t->Id,
                    'label' => $t->Code,
                    'isActive' => is_null($t->pivot->DeletedOn),
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error("Role Toggle Failed: " . $e->getMessage());

            return response()->json(['message' => 'Action failed.'], 500);
        }
    }

    public function destroy(): JsonResponse
    {
        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                // Suspend account instead of deleting for future reactivation.
                $user->update([
                    'IsActive' => false,
                    'ModifiedBy' => $user->Id,
                    'ModifiedOn' => now(),
                ]);

                // Revoke all access tokens immediately after suspension.
                $user->tokens()->delete();
            });

            return response()->json([
                'message' => 'Profile suspended successfully.',
                'status' => 'suspended',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Profile Suspension Failed [User: {$user->Id}]: " . $e->getMessage());

            return response()->json(['message' => __('auth.profile_delete_failed')], 500);
        }
    }
}
