<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\StoreBankDetailsRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateBankDetailsRequest;
use App\Http\Resources\ThirdParty\ThirdPartyBankDetailCollection;
use App\Http\Resources\ThirdParty\ThirdPartyBankDetailResource;
use App\Models\ThirdParty\ThirdPartiesBankDetails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ThirdPartiesBankDetailsController extends Controller
{
    public function index(): ThirdPartyBankDetailCollection
    {
        $user = Auth::user();

        if (! $user || ! $user->thirdParty) {
            abort(Response::HTTP_UNAUTHORIZED, 'Not authorized to view bank details.');
        }

        $bankDetails = ThirdPartiesBankDetails::where('ThirdPartyID', $user->thirdParty->Id)
            ->with('currency')
            ->get();

        return new ThirdPartyBankDetailCollection($bankDetails);
    }

    public function store(StoreBankDetailsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $bankDetail = ThirdPartiesBankDetails::create(array_merge(
            $request->validated(),
            ['CreatedBy' => $user->Id]
        ));

        return response()->json(new ThirdPartyBankDetailResource($bankDetail->load('currency')), Response::HTTP_CREATED);
    }

    public function show(ThirdPartiesBankDetails $thirdPartiesBankDetail): ThirdPartyBankDetailResource
    {
        $user = Auth::user();

        if (! $user || ! $user->thirdParty || $user->thirdParty->Id !== $thirdPartiesBankDetail->ThirdPartyId) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to view this bank detail.');
        }

        return new ThirdPartyBankDetailResource($thirdPartiesBankDetail->load('currency'));
    }

    public function update(UpdateBankDetailsRequest $request, ThirdPartiesBankDetails $thirdPartiesBankDetail): ThirdPartyBankDetailResource
    {
        $user = Auth::user();

        $thirdPartiesBankDetail->update(array_merge(
            $request->validated(),
            ['ModifiedBy' => $user->Id]
        ));

        return new ThirdPartyBankDetailResource($thirdPartiesBankDetail->load('currency'));
    }

    public function destroy(ThirdPartiesBankDetails $thirdPartiesBankDetail): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->thirdParty || $user->thirdParty->Id !== $thirdPartiesBankDetail->ThirdPartyId) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to delete this bank detail.');
        }

        $thirdPartiesBankDetail->update([
            'DeletedBy' => $user->Id,
        ]);

        $thirdPartiesBankDetail->delete();

        return response()->noContent();
    }
}
