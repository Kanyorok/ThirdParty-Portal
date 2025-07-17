<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartiesBankDetails;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ThirdPartiesBankDetailsController extends Controller
{
    public function index(): JsonResponse
    {
        $bankDetails = ThirdPartiesBankDetails::all();

        return response()->json($bankDetails);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ThirdPartyID' => 'required|integer|exists:t_ThirdParties,Id',
            'BankName' => 'required|string|max:255',
            'Branch' => 'nullable|string|max:255',
            'AccountNumber' => 'required|string|max:100|unique:t_ThirdPartiesBankDetails,AccountNumber',
            'Currency' => 'nullable|string|max:10',
            'SwiftCode' => 'nullable|string|max:50',
        ]);

        $bankDetail = ThirdPartiesBankDetails::create([
            'ThirdPartyID' => $request->ThirdPartyID,
            'BankName' => $request->BankName,
            'Branch' => $request->Branch,
            'AccountNumber' => $request->AccountNumber,
            'Currency' => $request->Currency,
            'SwiftCode' => $request->SwiftCode,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return response()->json($bankDetail, 201);
    }

    public function show(ThirdPartiesBankDetails $thirdPartiesBankDetail): JsonResponse
    {
        return response()->json($thirdPartiesBankDetail);
    }

    public function update(Request $request, ThirdPartiesBankDetails $thirdPartiesBankDetail): JsonResponse
    {
        $request->validate([
            'ThirdPartyID' => 'sometimes|required|integer|exists:t_ThirdParties,Id',
            'BankName' => 'sometimes|required|string|max:255',
            'Branch' => 'nullable|string|max:255',
            'AccountNumber' => 'sometimes|required|string|max:100|unique:t_ThirdPartiesBankDetails,AccountNumber,' . $thirdPartiesBankDetail->BankID . ',BankID',
            'Currency' => 'nullable|string|max:10',
            'SwiftCode' => 'nullable|string|max:50',
        ]);

        $thirdPartiesBankDetail->update([
            'ThirdPartyID' => $request->input('ThirdPartyID', $thirdPartiesBankDetail->ThirdPartyID),
            'BankName' => $request->input('BankName', $thirdPartiesBankDetail->BankName),
            'Branch' => $request->input('Branch', $thirdPartiesBankDetail->Branch),
            'AccountNumber' => $request->input('AccountNumber', $thirdPartiesBankDetail->AccountNumber),
            'Currency' => $request->input('Currency', $thirdPartiesBankDetail->Currency),
            'SwiftCode' => $request->input('SwiftCode', $thirdPartiesBankDetail->SwiftCode),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return response()->json($thirdPartiesBankDetail);
    }

    public function destroy(ThirdPartiesBankDetails $thirdPartiesBankDetail): JsonResponse
    {
        $thirdPartiesBankDetail->update([
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now(),
        ]);
        $thirdPartiesBankDetail->delete();

        return response()->json(null, 204);
    }
}
