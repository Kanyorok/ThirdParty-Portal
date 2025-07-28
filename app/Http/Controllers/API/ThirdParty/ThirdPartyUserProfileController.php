<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyUserProfileRequest as ThirdPartyAuthUpdateThirdPartyUserProfileRequest;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class ThirdPartyUserProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $user->load('thirdParty');

        return response()->json([
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

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

    public function partialUpdate(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.3rd_party_not_found')], 404);
        }

        try {
            $rules = [
                'firstName' => ['sometimes', 'string', 'max:50', 'regex:/^[a-zA-Z\s\'-]+$/'],
                'lastName' => ['sometimes', 'string', 'max:50', 'regex:/^[a-zA-Z\s\'-]+$/'],
                'phone' => ['sometimes', 'string', 'min:10', 'max:15', 'regex:/^\+?[\d\s\-\(\)]+$/'],
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
            ];

            $validatedData = $request->validate($rules);

            if (empty($validatedData)) {
                return response()->json(['message' => __('auth.invalid_fields')], 400);
            }

            DB::transaction(function () use ($user, $thirdParty, $validatedData) {
                $userUpdateFields = [];
                $thirdPartyUpdateFields = [];

                if (isset($validatedData['firstName'])) {
                    $userUpdateFields['FirstName'] = $validatedData['firstName'];
                }
                if (isset($validatedData['lastName'])) {
                    $userUpdateFields['LastName'] = $validatedData['lastName'];
                }
                if (isset($validatedData['phone'])) {
                    $userUpdateFields['Phone'] = $validatedData['phone'];
                }
                if (isset($validatedData['email'])) {
                    $userUpdateFields['Email'] = $validatedData['email'];
                }
                if (isset($validatedData['gender'])) {
                    $userUpdateFields['Gender'] = $validatedData['gender'];
                }
                if (isset($validatedData['imageId'])) {
                    $userUpdateFields['ImageId'] = $validatedData['imageId'];
                }

                if (!empty($userUpdateFields)) {
                    $userUpdateFields['ModifiedBy'] = Auth::id();
                    $userUpdateFields['ModifiedOn'] = now();
                    $user->fill($userUpdateFields)->save();
                }

                if (isset($validatedData['tradingName'])) {
                    $thirdPartyUpdateFields['TradingName'] = $validatedData['tradingName'];
                }
                if (isset($validatedData['businessType'])) {
                    $thirdPartyUpdateFields['BusinessType'] = $validatedData['businessType'];
                }
                if (isset($validatedData['registrationNumber'])) {
                    $thirdPartyUpdateFields['RegistrationNumber'] = $validatedData['registrationNumber'];
                }
                if (isset($validatedData['taxPin'])) {
                    $thirdPartyUpdateFields['TaxPIN'] = $validatedData['taxPin'];
                }
                if (isset($validatedData['vatNumber'])) {
                    $thirdPartyUpdateFields['VATNumber'] = $validatedData['vatNumber'];
                }
                if (isset($validatedData['country'])) {
                    $thirdPartyUpdateFields['Country'] = $validatedData['country'];
                }
                if (isset($validatedData['physicalAddress'])) {
                    $thirdPartyUpdateFields['PhysicalAddress'] = $validatedData['physicalAddress'];
                }
                if (isset($validatedData['website'])) {
                    $thirdPartyUpdateFields['Website'] = $validatedData['website'];
                }

                if (!empty($thirdPartyUpdateFields)) {
                    $thirdPartyUpdateFields['ModifiedBy'] = Auth::id();
                    $thirdPartyUpdateFields['ModifiedOn'] = now();
                    $thirdParty->fill($thirdPartyUpdateFields)->save();
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

        $user->load('thirdParty');

        return response()->json([
            'message' => __('auth.profile_update_ok'),
            'user_profile' => new ThirdPartyUserResource($user),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
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

    public function destroy(Request $request): JsonResponse
    {
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
