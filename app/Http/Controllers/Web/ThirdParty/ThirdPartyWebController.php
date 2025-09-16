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
            $query = ThirdParties::query()->with('types')
                ->select([
                    'Id',
                    'ThirdPartyName',
                    'Country',
                    'ThirdPartyType', // legacy
                    'ApprovalStatus',
                    'BusinessType',
                    'IsPrequalified',
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
                ->addColumn('IsPrequalified', fn(ThirdParties $thirdParty) => (bool) $thirdParty->IsPrequalified)
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
        return view('thirdparty.parties.show', compact('party'));
    }

    public function edit(ThirdParties $party): View
    {
        $businessTypes = BusinessTypeEnum::cases();
        $approvalStatuses = ThirdPartyApprovalStatusEnum::cases();
        $party->loadMissing('types');
        return view('thirdparty.parties.edit', compact('party', 'businessTypes', 'approvalStatuses'));
    }

    public function update(UpdateThirdPartyRequest $request, ThirdParties $party): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['ModifiedBy'] = Auth::id();

            $party->update($data);

            // If the party status was set to Active, ensure linked users are activated
            if (array_key_exists('Status', $data) && $data['Status'] === ThirdPartyStatusEnum::Active->value) {
                ThirdPartyUser::where('ThirdPartyId', $party->Id)
                    ->update(['IsActive' => 1, 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
            }

            return redirect()->route('thirdparty.parties.show', ['party' => $party->Id])
                ->with('success', 'Third party information updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update third party: ' . $e->getMessage(), ['partyId' => $party->Id, 'request_data' => $request->all()]);
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
}
