<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\ThirdParty\ThirdPartiesBankDetails;
use App\Http\Requests\ThirdPartyBankDetail\StoreBankDetailsRequest;
use App\Http\Requests\ThirdPartyBankDetail\UpdateBankDetailsRequest;
use App\Models\ThirdParty\ThirdParties;

class ThirdPartiesBankDetailsController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $bankDetails = ThirdPartiesBankDetails::all();
        } elseif ($user->thirdParty) {
            $bankDetails = ThirdPartiesBankDetails::where('ThirdPartyID', $user->thirdParty->Id)->get();
        } else {
            return response()->json([], 403);
        }

        return response()->json($bankDetails);
    }

    public function store(StoreBankDetailsRequest $request): JsonResponse
    {
        $thirdParty = ThirdParties::findOrFail($request->input('ThirdPartyId'));
        $this->authorize('create', $thirdParty);

        $bankDetail = ThirdPartiesBankDetails::create([
            'ThirdPartyID' => $thirdParty->Id,
            'BankName' => $request->input('BankName'),
            'Branch' => $request->input('Branch'),
            'AccountNumber' => $request->input('AccountNumber'),
            'CurrencyId' => $request->input('CurrencyId'),
            'SwiftCode' => $request->input('SwiftCode'),
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return response()->json($bankDetail, 201);
    }

    public function show(ThirdPartiesBankDetails $thirdPartiesBankDetail): JsonResponse
    {
        $this->authorize('view', $thirdPartiesBankDetail);

        return response()->json($thirdPartiesBankDetail);
    }

    public function update(UpdateBankDetailsRequest $request, ThirdPartiesBankDetails $thirdPartiesBankDetail): JsonResponse
    {
        $this->authorize('update', $thirdPartiesBankDetail);

        $thirdPartiesBankDetail->update([
            'BankName' => $request->input('BankName', $thirdPartiesBankDetail->BankName),
            'Branch' => $request->input('Branch', $thirdPartiesBankDetail->Branch),
            'AccountNumber' => $request->input('AccountNumber', $thirdPartiesBankDetail->AccountNumber),
            'CurrencyId' => $request->input('CurrencyId', $thirdPartiesBankDetail->CurrencyId),
            'SwiftCode' => $request->input('SwiftCode', $thirdPartiesBankDetail->SwiftCode),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return response()->json($thirdPartiesBankDetail);
    }

    public function destroy(ThirdPartiesBankDetails $thirdPartiesBankDetail): JsonResponse
    {
        $this->authorize('delete', $thirdPartiesBankDetail);

        $thirdPartiesBankDetail->update([
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now(),
        ]);

        $thirdPartiesBankDetail->delete();

        return response()->json(null, 204);
    }
}
