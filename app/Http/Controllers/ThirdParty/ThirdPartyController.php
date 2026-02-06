<?php

namespace App\Http\Controllers\ThirdParty;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\NewThirdPartyRequest;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\Procurement\Order;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\Workflow\CodeDetail;
use App\Services\LocalityService;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

class ThirdPartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', ThirdParties::class);
        if ($request->ajax()) {
            try {
                $query = ThirdParties::query()
                    ->with(['businessType:Id,Description', 'country:Id,Name,Flag', 'types:TypeId,Code,Description', 'status:Id,Description']);

                return DataTables::of($query)->editColumn('types', function (ThirdParties $thirdParties) {
                    return $thirdParties->types->pluck('Description')->map(fn ($type) => "<span class='badge bg-primary'>{$type}</span>")->implode(' ');
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (ThirdParties $thirdParties) {
                        return route('thirdparty.parties.show', $thirdParties->Id);
                    },
                ])->addIndexColumn()->rawColumns(['types'])->make();
            } catch (Throwable $e) {
                Log::error('Failed to load third parties: ' . $e->getMessage());

                return $this->errored('unexpected error occurred while loading the data. please try again later.');
            }
        }

        return view('thirdparty.index', [
            'types' => ThirdPartyType::query()->get(['Code', 'Description']),
            'businessTypes' => CodeDetail::query()->where('CodeID', 'BusinessType')->orderBy('DisplayOrder')->get(['Value', 'Description']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewThirdPartyRequest $request)
    {
        $this->authorize('create', ThirdParties::class);
        $actor = $request->user();
        $country = $request->getCountry();
        $businessType = $request->getBusinessType();
        $location = $request->getLocation($country);
        $phone = $request->getPhoneNumber($country, 'Phone');
        $logo = $request->getLogo();
        $userDetails = [];
        if ($request->boolean('createUser')) {
            $userDetails = [
                'FirstName' => $request->str('user_FirstName')->trim()->toString(),
                'LastName' => $request->str('user_LastName')->trim()->toString(),
                'Email' => $request->str('user_Email')->trim()->toString(),
                'Phone' => $request->getPhoneNumber($country, 'user_Phone'),
                'Gender' => $request->getGender('user_Gender'),
            ];
        }
        $customerDetails = [];
        if (in_array(ThirdPartyService::TypeCustomer, $request->array('types'), true)) {
            $customerDetails = [
                'DateOfBirth' => $request->date('customer_DateOfBirth'),
                'Gender' => $request->getGender('customer_Gender'),
                'MaritalStatus' => $request->getMaritalStatus('customer_MaritalStatus'),
                'Occupation' => $request->getOccupation('customer_Occupation'),
            ];
        }
        $tenantDetails = [];
        if (in_array(ThirdPartyService::TypeTenant, $request->array('types'), true)) {
            $tenantDetails = [
                'Remarks' => $request->str('tenant_Remarks')->trim()->toString(),
            ];
        }

        try {
            return DB::transaction(function () use ($request, $actor, $businessType, $location, $phone, $userDetails, $customerDetails, $tenantDetails, $logo) {

                $party = ThirdPartyService::create(
                    name: $request->str('Name')->trim()->toString(),
                    tradingName: $request->str('TradingName')->trim()->toString(),
                    businessType: $businessType,
                    registrationNumber: $request->str('RegistrationNumber')->trim()->toString(),
                    taxPIN: $request->str('TaxPIN')->trim()->toString(),
                    vatNumber: $request->str('VATNumber')->trim()->toString(),
                    locationID: $location,
                    physicalAddress: $request->str('PhysicalAddress')->trim()->toString(),
                    email: $request->str('Email')->trim()->toString(),
                    phone: $phone,
                    website: $request->str('Website')->trim()->toString(),
                    status: null,
                    extra: null,
                    actor: $actor,
                    data: [
                        'types' => $request->array('types'),
                        'user_DateOfBirth' => $customerDetails['DateOfBirth'] ?? null,
                        'user_Gender' => $customerDetails['Gender'] ?? null,
                        'user_MaritalStatus' => $customerDetails['MaritalStatus'] ?? null,
                        'user_Occupation' => $customerDetails['Occupation'] ?? null,
                        'tenant_Remarks' => $tenantDetails['Remarks'] ?? null,
                    ],
                );

                // Create service wrapper for additional operations
                $service = new ThirdPartyService($party);

                if ($request->boolean('createUser')) {
                    $service->addUser(
                        firstName: $userDetails['FirstName'],
                        lastName: $userDetails['LastName'],
                        email: $userDetails['Email'],
                        phone: $userDetails['Phone'],
                        gender: $userDetails['Gender'],
                        actor: $actor,
                        password: $request->get('user_Password') ?? null
                    );
                }

                if ($logo instanceof UploadedFile) {
                    $service->setLogo($logo, $actor);
                }

                Log::info('Created ThirdParty:', ['party' => $party, 'id' => $party->Id]);

                return $this->succeeded("{$party->ThirdPartyName} created successfully", route('thirdparty.parties.show', $party->Id));
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable $e) {
            Log::error('Failed to create third party: ');
            Log::error($e);
        }

        return $this->errored('unexpected error occurred while creating the third party. please try again later.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ThirdParties::class);

        return view('thirdparty.create', [
            'types' => ThirdPartyType::query()->get(['Code', 'Description']),
            'countries' => Country::query()->select(['Name', 'CountryCode', 'Id', 'PhoneCode', 'Flag'])->whereHas('localities')->orderBy('t_Countries.Name')->get(),
            'businessTypes' => CodeDetail::query()->where('CodeID', 'BusinessType')->orderBy('DisplayOrder')->get(['Value', 'Description']),
            'genders' => CodeDetail::where('CodeID', 'Gender')->get(['Value', 'Description']),
            'maritalstatus' => CodeDetail::where('CodeID', 'MaritalStatus')->get(['Value', 'Description']),
            'occupations' => CodeDetail::where('CodeID', 'Occupation')->get(['Value', 'Description']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($thirdPartyId): \Illuminate\Http\RedirectResponse|View
    {
        $thirdParty = ThirdParties::query()->where('Id', $thirdPartyId)
            ->with([
                'types',
                'country:Id,Name,Flag,PhoneCode',
                'businessType:Id,Description',
                'status:Id,Description',
                'location',
            ])->first();

        if (! $thirdParty instanceof ThirdParties) {
            return redirect()->back()->with('error', 'Third party not found');
        }

        $this->authorize('view', $thirdParty);

        $supplierStats = null;
        if ($thirdParty->isSupplier()) {
            $supplierStats = [
                'active_orders' => Order::where('AccountID', $thirdPartyId)->whereNull('DeletedOn')->count(),
                'total_order_value' => Order::where('AccountID', $thirdPartyId)->whereNull('DeletedOn')->sum('TotalAmount'),
                'pending_tenders' => SupplierMaster::where('ThirdPartyId', $thirdPartyId)->first()?->prequalificationApplications()->count() ?? 0,
            ];
        }

        $tenantStats = null;
        if ($thirdParty->isTenant()) {
            $tenantQuery = PropertyNewLease::whereHas('tenant', fn ($q) => $q->where('ThirdPartyId', $thirdPartyId));
            $tenantStats = [
                'active_leases' => (clone $tenantQuery)->where('Status', 'Active')->count(),
                'monthly_rent_roll' => (clone $tenantQuery)->where('Status', 'Active')->sum('MonthlyRent'),
                // Placeholder for pending rent until PropertyInvoice is confirmed
                'pending_rent' => 0,
            ];
        }

        $customerStats = null;
        if ($thirdParty->isCustomer()) {
            // Placeholder: Just verify existence for now
            $customerStats = [
                'active' => true,
            ];
        }

        return view('thirdparty.show', [
            'party' => $thirdParty,
            'location' => ($thirdParty->location instanceof Locality) ? (new LocalityService($thirdParty->location))->getLocation() : '',
            'supplierStats' => $supplierStats,
            'tenantStats' => $tenantStats,
            'customerStats' => $customerStats,
        ])
            ->with('party', $thirdParty);
    }

    /**
     * Handle Attrition / Deactivation
     */
    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        try {
            $party = ThirdParties::findOrFail($id);
            $this->authorize('delete', $party);

            DB::transaction(function () use ($party, $request) {
                // 1. Update status to Inactive (Assuming ID 2 is Inactive, or just rely on soft delete)
                // We'll verify status codes later, for now we rely on SoftDeletes as the primary "Deactivation"

                // 2. Log the reason in Extra field
                $extra = $party->Extra ?? [];
                $extra['deactivation_reason'] = $request->reason;
                $extra['deactivated_by'] = auth()->id();
                $extra['deactivated_at'] = now()->toDateTimeString();
                $party->update(['Extra' => $extra]);

                // 3. Soft Delete the main record
                $party->delete();

                // 4. Update Pivot tables (Soft delete connections)
                $party->types()->newPivotStatement()
                    ->where('ThirdPartyId', $party->Id)
                    ->update([
                        'DeletedOn' => now(),
                        'DeletedBy' => auth()->id(),
                    ]);
            });

            return redirect()->route('thirdparty.parties.index')
                ->with('success', 'Third Party has been successfully deactivated (Attrited).');
        } catch (\Exception $e) {
            Log::error("Attrition failed for ID $id: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to process attrition. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThirdParties $thirdParties)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThirdParties $thirdParties)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($thirdPartyId)
    {
        try {
            $thirdParty = ThirdParties::findOrFail($thirdPartyId);
            $this->authorize('delete', $thirdParty);
            $thirdPartyName = $thirdParty->ThirdPartyName;
            $thirdParty->delete();

            return response()->json([
                'success' => true,
                'message' => "Third party '{$thirdPartyName}' deleted successfully",
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Third party not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete third party: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete third party. Please try again.',
            ], 500);
        }
    }

    public function searchExisting(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ThirdParties::class);
        $search = $request->get('q');
        $excludeType = $request->get('type');
        // Log::info("Search Existing Params: q={$search}, type={$excludeType}");

        $query = ThirdParties::query()
            ->select('Id', 'ThirdPartyName', 'TradingName', 'Email')
            ->where(function ($q) use ($search) {
                $q->where('ThirdPartyName', 'like', "%{$search}%")
                    ->orWhere('TradingName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%");
            });

        if ($excludeType) {
            $query->whereDoesntHave('types', function ($q) use ($excludeType) {
                $q->where('Code', $excludeType);
            });
        }

        $results = $query->limit(20)->get()->map(function ($party) {
            return [
                'id' => $party->Id,
                'text' => $party->ThirdPartyName . ($party->TradingName ? " ({$party->TradingName})" : "") . " - {$party->Email}",
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function addRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'third_party_id' => 'required|exists:t_ThirdParties,Id',
            'type' => 'required|in:SU,TN,CU',
            'tenant_Remarks' => 'required_if:type,TN|nullable|string',
            'customer_DateOfBirth' => 'required_if:type,CU|nullable|date',
            'customer_Gender' => 'required_if:type,CU|nullable|exists:t_Core_Approval_CodeDetails,Value',
            'customer_MaritalStatus' => 'required_if:type,CU|nullable|exists:t_Core_Approval_CodeDetails,Value',
            'customer_Occupation' => 'required_if:type,CU|nullable|exists:t_Core_Approval_CodeDetails,Value',
        ]);

        $thirdParty = ThirdParties::findOrFail($validated['third_party_id']);
        $this->authorize('update', $thirdParty);
        $service = new ThirdPartyService($thirdParty);
        $actor = $request->user();

        try {
            DB::transaction(function () use ($service, $actor, $validated) {
                match ($validated['type']) {
                    'SU' => $service->addSupplier($actor),
                    'TN' => $service->addTenant($actor, $validated['tenant_Remarks']),
                    'CU' => $service->addCustomer(
                        Referral: null,
                        DateOfBirth: new \DateTime($validated['customer_DateOfBirth']),
                        Gender: CodeDetail::where('Value', $validated['customer_Gender'])->firstOrFail(),
                        MaritalStatus: CodeDetail::where('Value', $validated['customer_MaritalStatus'])->firstOrFail(),
                        Occupation: CodeDetail::where('Value', $validated['customer_Occupation'])->firstOrFail(),
                        actor: $actor
                    ),
                };
            });

            return response()->json([
                'success' => true,
                'message' => 'Role added successfully',
                'redirect' => route('thirdparty.parties.show', $thirdParty->Id),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to add role: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
