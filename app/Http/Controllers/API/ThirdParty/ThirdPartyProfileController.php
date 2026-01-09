<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyProfileRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyResource;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log, Hash};
use Illuminate\Validation\ValidationException;

class ThirdPartyProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = Auth::user()->load(['thirdParty.categories', 'thirdParty.country', 'thirdParty.types']);

        if (!$user->thirdParty) {
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

        if (!$thirdParty) {
            return response()->json(['message' => __('auth.third_party_not_linked')], 404);
        }

        try {
            DB::transaction(function () use ($user, $thirdParty, $request) {
                $data = $request->validated();

                $user->update(array_filter([
                    'FirstName' => $data['firstName'] ?? null,
                    'LastName'  => $data['lastName'] ?? null,
                    'Phone'     => $data['phone'] ?? null,
                ]));

                $thirdParty->update(array_filter([
                    'TradingName'        => $data['tradingName'] ?? null,
                    'BusinessType'       => $data['businessType'] ?? null,
                    'RegistrationNumber' => $data['registrationNumber'] ?? null,
                    'TaxPIN'             => $data['taxPin'] ?? null,
                    'VATNumber'          => $data['vatNumber'] ?? null,
                    'CountryId'          => $data['countryId'] ?? null,
                    'PhysicalAddress'    => $data['physicalAddress'] ?? null,
                    'Website'            => $data['website'] ?? null,
                    'ModifiedBy'         => $user->Id,
                    'ModifiedOn'         => now(),
                ]));

                if (isset($data['categories'])) {
                    $thirdParty->categories()->sync($data['categories']);
                }
            });

            return response()->json([
                'message'      => __('auth.profile_update_ok'),
                'user_profile' => new ThirdPartyResource($thirdParty->refresh()->load(['categories', 'country', 'types'])),
            ]);
        } catch (\Exception $e) {
            Log::error("Profile Update Failed [User: {$user->Id}]: " . $e->getMessage());
            return response()->json(['message' => __('auth.profile_update_failed')], 500);
        }
    }
    public function changePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->Password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth.password_mismatch')],
            ]);
        }

        $user->update([
            'Password'   => Hash::make($request->new_password),
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
                    'DeletedOn'  => $enable ? null : now(),
                    'DeletedBy'  => $enable ? null : $user->Id,
                    'ModifiedOn' => now(),
                    'ModifiedBy' => $user->Id
                ]);

            return response()->json([
                'message' => $enable ? 'Role authorized.' : 'Role access revoked.',
                'roles'   => $thirdParty->refresh()->load('types')->types->map(fn($t) => [
                    'id'       => $t->Id,
                    'label'    => $t->Code,
                    'isActive' => is_null($t->pivot->DeletedOn),
                ])
            ]);
        } catch (\Exception $e) {
            Log::error("Role Toggle Failed: " . $e->getMessage());
            return response()->json(['message' => 'Action failed.'], 500);
        }
    }
}
