<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThirdPartyProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json([
                'success' => false,
                'message' => 'Third party profile not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'thirdPartyName' => ['sometimes', 'string', 'max:255'],
            'tradingName' => ['sometimes', 'string', 'max:255'],
            'businessType' => ['sometimes', 'exists:t_CodeDetails,Id'],
            'registrationNumber' => ['sometimes', 'string', 'max:100'],
            'taxPIN' => ['sometimes', 'string', 'max:100'],
            'countryId' => ['sometimes', 'exists:t_Countries,Id'],
            'locationId' => ['sometimes', 'exists:t_Localities,ID'],
            'physicalAddress' => ['sometimes', 'string', 'max:500'],
            'website' => ['sometimes', 'url', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $mapping = [
            'thirdPartyName' => 'ThirdPartyName',
            'tradingName' => 'TradingName',
            'businessType' => 'BusinessType',
            'registrationNumber' => 'RegistrationNumber',
            'taxPIN' => 'TaxPIN',
            'countryId' => 'CountryId',
            'locationId' => 'LocationId',
            'physicalAddress' => 'PhysicalAddress',
            'website' => 'Website',
        ];

        $updateData = [];
        foreach ($mapping as $requestKey => $columnName) {
            if ($request->has($requestKey)) {
                $updateData[$columnName] = $request->get($requestKey);
            }
        }

        $thirdParty->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new ThirdPartyResource($thirdParty->load(['businessType', 'supplierMaster.status', 'types']))
        ]);
    }
}
