<?php

namespace App\Http\Controllers\Web\ThirdParty;

use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\ThirdPartyTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\StoreThirdPartyWithUserRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use Illuminate\Support\Facades\DB;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThirdPartyWebController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = ThirdParties::query()
                ->with(['types', 'country'])
                ->select([
                    't_ThirdParties.Id',
                    't_ThirdParties.ThirdPartyName',
                    't_ThirdParties.TradingName',
                    't_ThirdParties.CountryId',
                    't_ThirdParties.ThirdPartyType',
                    't_ThirdParties.ApprovalStatus',
                    't_ThirdParties.BusinessType',
                    't_ThirdParties.IsPrequalified',
                ])
                ->addSelect([
                    'UserEmail' => ThirdPartyUser::select('Email')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_ThirdParties.Id')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                    'UserFirstName' => ThirdPartyUser::select('FirstName')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_ThirdParties.Id')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                    'UserLastName' => ThirdPartyUser::select('LastName')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_ThirdParties.Id')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                ]);

            // 🔍 Search
            if ($request->filled('search.value')) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('ThirdPartyName', 'like', "%{$searchTerm}%")
                        ->orWhere('TradingName', 'like', "%{$searchTerm}%")
                        ->orWhere('RegistrationNumber', 'like', "%{$searchTerm}%")
                        ->orWhere('TaxPIN', 'like', "%{$searchTerm}%");
                });
            }

            // 🏷 Filter by type
            if ($request->filled('type')) {
                $typeFilter = $request->input('type');
                $query->whereHas('types', function ($q) use ($typeFilter) {
                    $q->where('TypeId', $typeFilter)
                        ->orWhere('Code', 'like', "%{$typeFilter}%");
                });
            }

            // ⚙️ Filter by approval status
            if ($request->filled('status')) {
                $query->where('ApprovalStatus', $request->input('status'));
            }

            try {
                return DataTables::of($query)
                    ->addColumn(
                        'checkbox',
                        fn(ThirdParties $tp) =>
                        '<input type="checkbox" name="selected[]" value="' . $tp->Id . '" class="form-check-input select-row">'
                    )

                    ->addColumn('ThirdPartyType', function (ThirdParties $thirdParty) {
                        $codes = $thirdParty->types->pluck('Description')->filter()->unique();
                        if ($codes->isNotEmpty()) {
                            return $codes->join(', ');
                        }

                        // Fallback: Enum safe conversion
                        return ThirdPartyTypeEnum::tryFrom($thirdParty->ThirdPartyType)?->label() ?? 'N/A';
                    })

                    ->addColumn('CountryId', fn($tp) => $tp->country->Name ?? '—')
                    ->addColumn('BusinessType', fn($row) => $row->BusinessType?->label())
                    ->addColumn('ApprovalStatus', fn($row) => $row->ApprovalStatus?->label())
                    ->addColumn('Status', fn($row) => $row->Status?->label())
                    ->addColumn('IsPrequalified', fn(ThirdParties $tp) => $tp->IsPrequalified ? 'Yes' : 'No')
                    ->addColumn(
                        'PrimaryUser',
                        fn(ThirdParties $tp) =>
                        trim(($tp->UserFirstName ?? '') . ' ' . ($tp->UserLastName ?? '')) ?: 'N/A'
                    )
                    ->addColumn('PrimaryEmail', fn(ThirdParties $tp) => $tp->UserEmail ?? 'N/A')
                    ->addColumn('actions', fn(ThirdParties $tp) => '
                    <div class="actions text-center">
                        <a href="' . route('thirdparty.parties.show', $tp->Id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="' . route('thirdparty.parties.edit', $tp->Id) . '" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="' . route('thirdparty.parties.destroy', $tp->Id) . '" method="POST" class="d-inline">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this record?\')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ')

                    ->rawColumns(['checkbox', 'actions'])
                    ->make(true);
            } catch (\Throwable $e) {
                Log::error('DataTables error in ThirdParty index: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'error' => 'An error occurred while loading the data: ' . $e->getMessage(),
                ], 500);
            }
        }

        return view('thirdparty.parties.index');
    }


    public function create(): View
    {
        $businessTypes = BusinessTypeEnum::cases();
        $approvalStatuses = ThirdPartyApprovalStatusEnum::cases();
        $thirdPartyTypes = ThirdPartyType::orderBy('Code')->get();
        $partyTypes = CodeDetail::where('CodeID', 'PartyType')->get();
        $country = Country::all();
        return view('thirdparty.parties.create', compact('businessTypes', 'approvalStatuses', 'thirdPartyTypes', 'partyTypes', 'country'));
    }

    public function store(StoreThirdPartyWithUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $partyType = strtolower($validated['PartyType'] ?? '');
            $thirdPartyName = $validated['ThirdPartyName'] ?? null;

            if ($partyType === 'in' || str_contains($partyType, 'individual')) {
                $thirdPartyName = trim($validated['FirstName'] . ' ' . $validated['LastName']);
            }

            $creatorId = DB::table('t_ThirdPartyUsers')->value('Id') ?? NULL;

            $thirdParty = ThirdParties::create([
                'ThirdPartyName'     => $thirdPartyName,
                'TradingName'        => $validated['TradingName'] ?? null,
                'BusinessType'       => $validated['BusinessType'] ?? null,
                'CountryId'          => $validated['Country'],
                'IDNumber'           => $validated['IDNumber'] ?? null,
                'PassportNo'         => $validated['PassportNo'] ?? null,
                'PhysicalAddress'    => $validated['PhysicalAddress'],
                'Email'              => $validated['Email'],
                'Phone'              => $validated['Phone'],
                'Website'            => $validated['Website'] ?? null,
                'ThirdPartyType'     => $validated['PartyType'],
                'RegistrationNumber' => $validated['RegistrationNumber'] ?? null,
                'TaxPIN'             => $validated['TaxPIN'] ?? null,
                'VATNumber'          => $validated['VATNumber'] ?? null,
                'ApprovalStatus'     => $validated['ApprovalStatus'] ?? null,
                'Status'             => $validated['Status'] ?? null,
                'CreatedBy'          => $creatorId,
            ]);

            $user = ThirdPartyUser::create([
                'UserID'       => strtoupper(Str::random(6)),
                'FirstName'    => $request->FirstName,
                'LastName'     => $request->LastName,
                'Email'        => $request->UserEmail,
                'Phone'        => $request->UserPhone,
                'Gender'       => $request->Gender,
                'Password'     => bcrypt('12345678'), // default password
                'IsActive'     => 1,
                'EmailVerifiedOn' => now(),
                'CreatedBy'    => $creatorId,
                'CreatedOn'    => now(),
                'ThirdPartyId' => $thirdParty->Id, // link to company
            ]);

            if (is_array($request->ThirdPartyType)) {
                foreach ($request->ThirdPartyType as $typeId) {
                    DB::table('t_ThirdPartyType_ThirdParties')->insert([
                        'TypeId'       => $typeId,
                        'ThirdPartyId' => $thirdParty->Id,
                        'CreatedBy'    => Auth::id() ?? 1,
                        'ModifiedBy'   => Auth::id() ?? 1,
                        'CreatedOn'    => now(),
                        'ModifiedOn'   => now(),
                    ]);
                }
            }


            DB::commit();

            return redirect()
                ->route('thirdparty.parties.index')
                ->with('success', 'Third Party and User created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Failed to create third party: ' . $e->getMessage());
        }
    }


    public function show(ThirdParties $party): View
    {
        $party->loadMissing('types');
        $primaryUser = ThirdPartyUser::where('ThirdPartyId', $party->Id)
            ->orderByDesc('CreatedOn')
            ->first();
        return view('thirdparty.parties.show', compact('party', 'primaryUser'));
    }

    public function edit($id): View
    {
        $thirdParty = ThirdParties::with('types')->findOrFail($id);
        $primaryUser = ThirdPartyUser::where('ThirdPartyId', $thirdParty->Id)->orderByDesc('CreatedOn')->first();


        return view('thirdparty.parties.edit', [
            'thirdParty' => $thirdParty,
            'partyTypes' => CodeDetail::where('CodeID', 'PartyType')->get(),
            'thirdPartyTypes' => ThirdPartyType::orderBy('Code')->get(),
            'country' => Country::all(),
            'businessTypes' => BusinessTypeEnum::cases(),
            'approvalStatuses' => ThirdPartyApprovalStatusEnum::cases(),
            'statuses' => ThirdPartyStatusEnum::cases(),
            'primaryUser' => $primaryUser
        ]);
    }

    public function update(UpdateThirdPartyRequest $request, ThirdParties $party): RedirectResponse
    {
        try {
            Log::info('ThirdParty Update Started', [
                'partyId' => $party->Id,
                'requestData' => $request->all(),
            ]);

            $data = $request->validated();
            $data['ModifiedBy'] = Auth::id();

            $party->update($data);

            Log::info('ThirdParty updated successfully', [
                'partyId' => $party->Id,
                'newApprovalStatus' => $party->ApprovalStatus,
                'newStatus' => $party->Status,
            ]);

            // ✅ Update linked users’ IsActive field according to Status or ApprovalStatus
            if (isset($data['Status'])) {
                $isActive = $data['Status'] === ThirdPartyStatusEnum::Active->value ? 1 : 0;
                ThirdPartyUser::where('ThirdPartyId', $party->Id)
                    ->update(['IsActive' => $isActive, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
            }

            if (isset($data['ApprovalStatus'])) {
                $isActive = $data['ApprovalStatus'] === ThirdPartyApprovalStatusEnum::Approved->value ? 1 : 0;
                ThirdPartyUser::where('ThirdPartyId', $party->Id)
                    ->update(['IsActive' => $isActive, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
            }

            return redirect()
                ->route('thirdparty.parties.show', $party->Id)
                ->with('success', 'Third party information updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update third party', [
                'partyId' => $party->Id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('thirdparty.parties.show', $party->Id)
                ->with('error', 'Failed to update third party information. Please try again.');
        }
    }

    public function destroy(ThirdParties $party): RedirectResponse
    {
        try {
            if ($party->DeletedBy === null && Auth::check()) {
                $party->DeletedBy = Auth::id();
                $party->save();
            }
            $party->delete();

            return redirect()->route('thirdparty.parties.index')
                ->with('success', 'Third party deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete third party: ' . $e->getMessage(), ['partyId' => $party->Id]);
            return redirect()->back()
                ->with('error', 'Failed to delete third party. Please try again.');
        }
    }

    /**
     * Handle bulk actions on multiple third parties
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject,activate,deactivate',
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*' => 'required|integer|exists:t_ThirdParties,Id'
        ]);

        try {
            $action = $request->input('action');
            $selectedItems = $request->input('selectedItems');
            $userId = Auth::id();
            $now = now();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::transaction(function () use ($action, $selectedItems, $userId, $now, &$successCount, &$errorCount, &$errors) {
                foreach ($selectedItems as $partyId) {
                    try {
                        $party = ThirdParties::findOrFail($partyId);

                        switch ($action) {
                            case 'approve':
                                if ($party->ApprovalStatus !== ThirdPartyApprovalStatusEnum::Approved) {
                                    $party->ApprovalStatus = ThirdPartyApprovalStatusEnum::Approved;
                                    $party->ModifiedBy = $userId;
                                    $party->ModifiedOn = $now;
                                    $party->save();

                                    // Activate users when approved
                                    ThirdPartyUser::where('ThirdPartyId', $partyId)
                                        ->update(['IsActive' => 1, 'ModifiedBy' => $userId, 'ModifiedOn' => $now]);
                                }
                                break;

                            case 'reject':
                                if ($party->ApprovalStatus !== ThirdPartyApprovalStatusEnum::Rejected) {
                                    $party->ApprovalStatus = ThirdPartyApprovalStatusEnum::Rejected;
                                    $party->ModifiedBy = $userId;
                                    $party->ModifiedOn = $now;
                                    $party->save();

                                    // Deactivate users when rejected
                                    ThirdPartyUser::where('ThirdPartyId', $partyId)
                                        ->update(['IsActive' => 0, 'ModifiedBy' => $userId, 'ModifiedOn' => $now]);
                                }
                                break;

                            case 'activate':
                                if ($party->Status !== ThirdPartyStatusEnum::Active) {
                                    $party->Status = ThirdPartyStatusEnum::Active;
                                    $party->ModifiedBy = $userId;
                                    $party->ModifiedOn = $now;
                                    $party->save();

                                    // Activate users when status is set to active
                                    ThirdPartyUser::where('ThirdPartyId', $partyId)
                                        ->update(['IsActive' => 1, 'ModifiedBy' => $userId, 'ModifiedOn' => $now]);
                                }
                                break;

                            case 'deactivate':
                                if ($party->Status !== ThirdPartyStatusEnum::Inactive) {
                                    $party->Status = ThirdPartyStatusEnum::Inactive;
                                    $party->ModifiedBy = $userId;
                                    $party->ModifiedOn = $now;
                                    $party->save();

                                    // Deactivate users when status is set to inactive
                                    ThirdPartyUser::where('ThirdPartyId', $partyId)
                                        ->update(['IsActive' => 0, 'ModifiedBy' => $userId, 'ModifiedOn' => $now]);
                                }
                                break;
                        }

                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Failed to update party {$partyId}: " . $e->getMessage();
                        Log::error("Bulk action failed for party {$partyId}", [
                            'action' => $action,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            $message = "Successfully processed {$successCount} item(s)";
            if ($errorCount > 0) {
                $message .= ". {$errorCount} item(s) failed to process.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'successCount' => $successCount,
                'errorCount' => $errorCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk action failed: ' . $e->getMessage(), [
                'action' => $request->input('action'),
                'selectedItems' => $request->input('selectedItems')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the bulk action. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
