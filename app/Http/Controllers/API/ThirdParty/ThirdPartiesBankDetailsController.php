<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\StoreBankDetailsRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateBankDetailsRequest;
use App\Http\Resources\ThirdParty\ThirdPartyBankDetailCollection;
use App\Http\Resources\ThirdParty\ThirdPartyBankDetailResource;
use App\Models\Finance\Bank;
use App\Models\Finance\BankBranch;
use App\Models\ThirdParty\ThirdPartiesBankDetails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ThirdPartiesBankDetailsController extends Controller
{
    public function index(): ThirdPartyBankDetailCollection
    {
        $user = Auth::user();

        if (! $user || ! $user->thirdParty) {
            abort(Response::HTTP_UNAUTHORIZED, 'Not authorized to view bank details.');
        }

        $bankDetails = ThirdPartiesBankDetails::where('ThirdPartyID', $user->thirdParty->Id)
            ->with(['currency', 'branch.bank'])
            ->get();

        return new ThirdPartyBankDetailCollection($bankDetails);
    }

    public function store(StoreBankDetailsRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->thirdParty) {
            abort(Response::HTTP_UNAUTHORIZED, 'Not authorized to create bank details.');
        }

        $payload = $request->validated();
        $payload['ThirdPartyId'] = (int) $user->thirdParty->Id;
        $payload['BranchID'] = $this->resolveBranchId(
            $payload['BranchID'] ?? null,
            $request->input('Branch'),
            $request->input('BankName')
        );

        if (! $payload['BranchID']) {
            throw ValidationException::withMessages([
                'BranchID' => ['BranchID is required. Provide `BranchID` (or `BranchId`) in the request payload.'],
            ]);
        }

        $bankDetail = ThirdPartiesBankDetails::create([
            'ThirdPartyId' => $payload['ThirdPartyId'],
            'BranchID' => $payload['BranchID'],
            'AccountNumber' => $payload['AccountNumber'],
            'CurrencyId' => $payload['CurrencyId'],
            'CreatedBy' => $user->Id,
            'Extra' => $this->buildLegacyExtra($request),
        ]);

        return response()->json(
            new ThirdPartyBankDetailResource($bankDetail->load(['currency', 'branch.bank'])),
            Response::HTTP_CREATED
        );
    }

    public function show(ThirdPartiesBankDetails $thirdPartiesBankDetail): ThirdPartyBankDetailResource
    {
        $user = Auth::user();

        if (! $user || ! $user->thirdParty || $user->thirdParty->Id !== $thirdPartiesBankDetail->ThirdPartyId) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to view this bank detail.');
        }

        return new ThirdPartyBankDetailResource($thirdPartiesBankDetail->load(['currency', 'branch.bank']));
    }

    public function update(UpdateBankDetailsRequest $request, ThirdPartiesBankDetails $thirdPartiesBankDetail): ThirdPartyBankDetailResource
    {
        $user = Auth::user();
        if (! $user || ! $user->thirdParty || $user->thirdParty->Id !== $thirdPartiesBankDetail->ThirdPartyId) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to update this bank detail.');
        }

        $payload = $request->validated();
        $updates = [
            'ModifiedBy' => $user->Id,
        ];

        if (array_key_exists('AccountNumber', $payload)) {
            $updates['AccountNumber'] = $payload['AccountNumber'];
        }
        if (array_key_exists('CurrencyId', $payload)) {
            $updates['CurrencyId'] = $payload['CurrencyId'];
        }
        if (array_key_exists('ThirdPartyId', $payload)) {
            $updates['ThirdPartyId'] = (int) $user->thirdParty->Id;
        }

        if (
            array_key_exists('BranchID', $payload)
            || $request->filled('Branch')
            || $request->filled('BankName')
        ) {
            $branchId = $this->resolveBranchId(
                $payload['BranchID'] ?? null,
                $request->input('Branch'),
                $request->input('BankName')
            );

            if (! $branchId) {
                throw ValidationException::withMessages([
                    'BranchID' => ['Unable to resolve BranchID. Provide a valid `BranchID` (or `BranchId`).'],
                ]);
            }

            $updates['BranchID'] = $branchId;
        }

        $legacyExtra = $this->buildLegacyExtra($request);
        if (! empty($legacyExtra)) {
            $updates['Extra'] = array_merge((array) ($thirdPartiesBankDetail->Extra ?? []), $legacyExtra);
        }

        $thirdPartiesBankDetail->update($updates);

        return new ThirdPartyBankDetailResource($thirdPartiesBankDetail->load(['currency', 'branch.bank']));
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

    private function resolveBranchId($branchId, ?string $branchName = null, ?string $bankName = null): ?int
    {
        if (! empty($branchId)) {
            return (int) $branchId;
        }

        $branchName = trim((string) $branchName);
        if ($branchName === '') {
            return null;
        }

        $query = BankBranch::query()->whereRaw('LOWER(LTRIM(RTRIM(BranchName))) = ?', [strtolower($branchName)]);

        $bankName = trim((string) $bankName);
        if ($bankName !== '') {
            $bankId = Bank::query()
                ->whereRaw('LOWER(LTRIM(RTRIM(BankName))) = ?', [strtolower($bankName)])
                ->value('BankID');
            if ($bankId) {
                $query->where('BankID', $bankId);
            }
        }

        $match = $query->orderBy('BranchID')->first();

        return $match ? (int) $match->BranchID : null;
    }

    private function buildLegacyExtra($request): array
    {
        $extra = [];
        if ($request->filled('BankName')) {
            $extra['bankName'] = (string) $request->input('BankName');
        }
        if ($request->filled('Branch')) {
            $extra['branch'] = (string) $request->input('Branch');
        }
        if ($request->filled('SwiftCode')) {
            $extra['swiftCode'] = (string) $request->input('SwiftCode');
        }

        return $extra;
    }
}
