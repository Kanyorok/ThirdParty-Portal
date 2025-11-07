<?php

namespace App\Http\Controllers\Web\ThirdParty;

use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\StoreThirdPartyRequest;
use App\Http\Requests\ThirdPartyAuth\UpdateThirdPartyRequest;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\DB;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class ThirdPartyWebController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = ThirdParties::query()
                ->with('types')
                ->select([
                    'Id',
                    'ThirdPartyName',
                    'TradingName',
                    'Country',
                    'ThirdPartyType', // legacy
                    'ApprovalStatus',
                    'BusinessType',
                    'IsPrequalified',
                ])
                // Attach primary associated ThirdPartyUser details (latest by CreatedOn)
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

            if ($request->filled('search.value')) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('ThirdPartyName', 'like', '%' . $searchTerm . '%')
                        ->orWhere('TradingName', 'like', '%' . $searchTerm . '%')
                        ->orWhere('Email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('Phone', 'like', '%' . $searchTerm . '%')
                        ->orWhere('RegistrationNumber', 'like', '%' . $searchTerm . '%')
                        ->orWhere('TaxPIN', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->filled('type')) {
                $typeFilter = $request->input('type');
                if (is_numeric($typeFilter)) {
                    $query->whereHas('types', fn($q) => $q->where('t_ThirdPartyTypes.TypeId', $typeFilter));
                } else {
                    $query->where('ThirdPartyType', $typeFilter); // legacy fallback
                }
            }

            if ($request->filled('status')) {
                $query->where('ApprovalStatus', $request->input('status'));
            }

            return DataTables::of($query)
                ->addColumn('ThirdPartyType', function (ThirdParties $thirdParty) {
                    $codes = $thirdParty->types->pluck('Code')->filter()->unique();
                    if ($codes->isNotEmpty()) {
                        return $codes->join(', ');
                    }
                    return $thirdParty->ThirdPartyType?->label() ?? 'N/A'; // legacy fallback
                })
                ->addColumn('BusinessType', fn(ThirdParties $thirdParty) => $thirdParty->BusinessType?->label() ?? 'N/A')
                ->addColumn('ApprovalStatus', fn(ThirdParties $thirdParty) => $thirdParty->ApprovalStatus?->label() ?? $thirdParty->ApprovalStatus?->value ?? 'N/A')
                ->addColumn('IsPrequalified', fn(ThirdParties $thirdParty) => (bool)$thirdParty->IsPrequalified)
                ->addColumn('PrimaryUser', fn(ThirdParties $thirdParty) => trim((string)($thirdParty->UserFirstName ?? '') . ' ' . (string)($thirdParty->UserLastName ?? '')) ?: 'N/A')
                ->addColumn('PrimaryEmail', fn(ThirdParties $thirdParty) => $thirdParty->UserEmail ?? 'N/A')
                ->addColumn('actions', fn(ThirdParties $thirdParty) => '<a href="' . route('thirdparty.parties.show', ['party' => $thirdParty->Id]) . '" class="btn btn-sm btn-info">View</a>')
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('thirdparty.parties.index');
    }

    public function create(): View
    {
        $businessTypes = BusinessTypeEnum::cases();
        $approvalStatuses = ThirdPartyApprovalStatusEnum::cases();
        return view('thirdparty.parties.create', compact('businessTypes', 'approvalStatuses'));
    }

    public function store(StoreThirdPartyRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $typeId = $data['ThirdPartyType'] ?? null; // numeric TypeId now
            unset($data['ThirdPartyType']);
            $party = ThirdParties::create($data);
            if ($typeId) {
                DB::table('t_ThirdPartyType_ThirdParties')->insert([
                    'TypeId' => $typeId,
                    'ThirdPartyId' => $party->Id,
                    'CreatedOn' => now(),
                ]);
            }
            return redirect()->route('thirdparty.parties.show', ['party' => $party->Id])
                ->with('success', 'Third party created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create third party: ' . $e->getMessage(), ['request_data' => $request->all()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create third party. Please try again.');
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

    public function edit(ThirdParties $party): View
    {
        $businessTypes = BusinessTypeEnum::cases();
        $approvalStatuses = ThirdPartyApprovalStatusEnum::cases();
        $party->loadMissing('types');
        $primaryUser = ThirdPartyUser::where('ThirdPartyId', $party->Id)
            ->orderByDesc('CreatedOn')
            ->first();
        return view('thirdparty.parties.edit', compact('party', 'businessTypes', 'approvalStatuses', 'primaryUser'));
    }

    public function update(UpdateThirdPartyRequest $request, ThirdParties $party): RedirectResponse
    {
        try {
            // Debug logging
            Log::info('ThirdParty Update Started', [
                'partyId' => $party->Id,
                'requestData' => $request->all(),
                'approvalStatus' => $request->input('ApprovalStatus'),
                'status' => $request->input('Status'),
                'totalParties' => ThirdParties::count()
            ]);

            $data = $request->validated();
            $data['ModifiedBy'] = Auth::id();

            Log::info('Validated data', [
                'partyId' => $party->Id,
                'validatedData' => $data
            ]);

            $party->update($data);

            Log::info('Party updated successfully', [
                'partyId' => $party->Id,
                'newApprovalStatus' => $party->ApprovalStatus,
                'newStatus' => $party->Status
            ]);

            // Sync linked users' IsActive based on Status and/or ApprovalStatus edits
            if (array_key_exists('Status', $data)) {
                if ($data['Status'] === ThirdPartyStatusEnum::Active->value) {
                    ThirdPartyUser::where('ThirdPartyId', $party->Id)
                        ->update(['IsActive' => 1, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                    Log::info('Users activated for party (via Status=Active)', [
                        'partyId' => $party->Id,
                        'affected' => ThirdPartyUser::where('ThirdPartyId', $party->Id)->count()
                    ]);
                } elseif ($data['Status'] === ThirdPartyStatusEnum::Inactive->value) {
                    ThirdPartyUser::where('ThirdPartyId', $party->Id)
                        ->update(['IsActive' => 0, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                    Log::info('Users deactivated for party (via Status=Inactive)', [
                        'partyId' => $party->Id,
                        'affected' => ThirdPartyUser::where('ThirdPartyId', $party->Id)->count()
                    ]);
                }
            }

            if (array_key_exists('ApprovalStatus', $data)) {
                if ($data['ApprovalStatus'] === ThirdPartyApprovalStatusEnum::Approved->value) {
                    ThirdPartyUser::where('ThirdPartyId', $party->Id)
                        ->update(['IsActive' => 1, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                    Log::info('Users activated for party (via ApprovalStatus=Approved)', [
                        'partyId' => $party->Id,
                        'affected' => ThirdPartyUser::where('ThirdPartyId', $party->Id)->count()
                    ]);
                } elseif ($data['ApprovalStatus'] === ThirdPartyApprovalStatusEnum::Rejected->value) {
                    ThirdPartyUser::where('ThirdPartyId', $party->Id)
                        ->update(['IsActive' => 0, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                    Log::info('Users deactivated for party (via ApprovalStatus=Rejected)', [
                        'partyId' => $party->Id,
                        'affected' => ThirdPartyUser::where('ThirdPartyId', $party->Id)->count()
                    ]);
                }
            }

            return redirect()->route('thirdparty.parties.show', ['party' => $party->Id])
                ->with('success', 'Third party information updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update third party: ' . $e->getMessage(), [
                'partyId' => $party->Id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('thirdparty.parties.show', ['party' => $party->Id])
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
