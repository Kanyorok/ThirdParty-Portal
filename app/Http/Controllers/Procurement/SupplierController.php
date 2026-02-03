<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StoreSupplierRequest;
use App\Http\Requests\Procurement\Suppliers\Prequalification\UpdateSupplierRequest;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\ThirdParties;
use App\Services\ThirdParties\SupplierWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function __construct(protected SupplierWorkflowService $workflowService)
    {
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SupplierMaster::query()
                ->with(['party', 'suppliers.category.itemCategories'])
                ->select('t_SupplierMaster.*')
                ->addSelect([
                    'PrimaryFirstName' => DB::table('t_ThirdPartyUsers')
                        ->select('FirstName')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_SupplierMaster.ThirdPartyId')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                    'PrimaryLastName' => DB::table('t_ThirdPartyUsers')
                        ->select('LastName')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_SupplierMaster.ThirdPartyId')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                    'PrimaryEmail' => DB::table('t_ThirdPartyUsers')
                        ->select('Email')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_SupplierMaster.ThirdPartyId')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                ]);



            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->filled('search.value')) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            // Search in Party details (Name, Trading Name, Email) and Associated Users (Contacts)
                            $q->whereHas('party', function ($pq) use ($searchValue) {
                                $pq->where('ThirdPartyName', 'like', "%{$searchValue}%")
                                    ->orWhere('TradingName', 'like', "%{$searchValue}%")
                                    ->orWhere('Email', 'like', "%{$searchValue}%")
                                    ->orWhereHas('users', function ($uq) use ($searchValue) {
                                        $uq->where('FirstName', 'like', "%{$searchValue}%")
                                            ->orWhere('LastName', 'like', "%{$searchValue}%")
                                            ->orWhere('Email', 'like', "%{$searchValue}%")
                                            ->orWhere(DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', "%{$searchValue}%");
                                    });
                            })
                            // Search in Assigned Categories
                            ->orWhereHas('suppliers.category', function ($cq) use ($searchValue) {
                                $cq->where('CategoryName', 'like', "%{$searchValue}%")
                                   ->orWhere('Description', 'like', "%{$searchValue}%");
                            });
                        });
                    }
                })
                ->addColumn('ThirdPartyName', function (SupplierMaster $supplier) {
                    return $supplier->party->ThirdPartyName ?? 'N/A';
                })
                ->addColumn('TradingName', function (SupplierMaster $supplier) {
                    return $supplier->party->TradingName ?? 'N/A';
                })
                ->addColumn('ApprovalStatus', function (SupplierMaster $supplier) {
                    return $supplier->ApprovalStatus ? $supplier->ApprovalStatus->label() : 'Pending';
                })
                ->addColumn('Prequalified', function (SupplierMaster $supplier) {
                    return $supplier->IsPrequalified ? 'Yes' : 'No';
                })
                ->addColumn('category_names', function (SupplierMaster $supplier) {
                    if (! $supplier->IsPrequalified) {
                        return '<span class="text-muted">Not prequalified</span>';
                    }
                    // Categories via active t_Suppliers entries (Prequalified)
                    $categories = $supplier->suppliers
                        ->where('Active_Status', true)
                        ->map(fn ($s) => $s->category)
                        ->filter()
                        ->unique('SupplierCategoryID');

                    if ($categories->isEmpty()) {
                        return '<span class="text-warning">No categories assigned</span>';
                    }

                    $html = '<dl class="mb-0">';
                    foreach ($categories as $cat) {
                        $catName = e($cat->CategoryName ?? $cat->Description ?? 'Category');
                        $itemCats = $cat->itemCategories ?? collect();
                        $count = $itemCats->count();
                        $badge = $count > 0 ? " <span class=\"badge bg-secondary ms-1\">{$count}</span>" : '';
                        $itemList = $count > 0 ? e($itemCats->pluck('Name')->filter()->unique()->implode(', ')) : 'No specific items';
                        $html .= "<dt class=\"fw-semibold\">{$catName}{$badge}</dt><dd class=\"mb-1\">{$itemList}</dd>";
                    }

                    return $html . '</dl>';
                })
                ->addColumn('PrimaryContact', function (SupplierMaster $supplier) {
                    $full = trim(($supplier->PrimaryFirstName ?? '') . ' ' . ($supplier->PrimaryLastName ?? ''));

                    return $full !== '' ? $full : 'N/A';
                })
                ->addColumn('PrimaryEmail', function (SupplierMaster $supplier) {
                    return $supplier->PrimaryEmail ?? $supplier->party->Email ?? 'N/A';
                })
                ->addColumn('actions', function (SupplierMaster $supplier) {
                    return '
                    <div class="d-flex gap-1">
                        ' . $this->getActionsButtons($supplier) . '
                    </div>
                ';
                })
                ->rawColumns(['actions', 'category_names'])
                ->make(true);
        }

        return view('procurement.suppliers.index');
    }

    public function search(Request $request)
    {
        $term = $request->get('q');

        $query = SupplierMaster::query()
            ->with('party')
            ->where('IsPrequalified', true)
            ->where('ApprovalStatus', \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Approved);

        if (! empty($term)) {
            $query->whereHas('party', function ($q) use ($term) {
                $q->where('ThirdPartyName', 'like', "%{$term}%")
                  ->orWhere('TradingName', 'like', "%{$term}%")
                  ->orWhere('RegistrationNumber', 'like', "%{$term}%");
            });
        }

        \Illuminate\Support\Facades\Log::info('Supplier Search Term: ' . $term);

        $suppliers = $query->limit(20)->get()->map(function ($supplier) {
            return [
                'id' => $supplier->Id,
                'text' => $supplier->party->ThirdPartyName ?? 'Unknown', // Select2 expects 'text' field
                'company_name' => $supplier->party->ThirdPartyName ?? 'Unknown',
                'registration_number' => $supplier->party->RegistrationNumber ?? 'N/A',
                // Add status for clarity in UI if needed, but Select2 text is simple
            ];
        })->values();

        \Illuminate\Support\Facades\Log::info('Supplier Search Result Count: ' . $suppliers->count());

        return response()->json($suppliers);
    }

    public function create()
    {
        $countries = \App\Models\Core\Country::all();
        $businessTypes = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'BusinessType')->get();
        $supplierCategories = \App\Models\ThirdParty\SupplierCategory::all();

        return view('procurement.suppliers.create', compact('countries', 'businessTypes', 'supplierCategories'));
    }

    public function store(StoreSupplierRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['CreatedBy'] = Auth::id();

        $supplier = ThirdParties::create($validatedData);
        // Attach supplier type via pivot (Code like SU-%). Pick first matching type.
        $supplierTypeId = DB::table('t_ThirdPartyTypes')->where('Code', 'like', 'SU-%')->value('TypeId');
        if ($supplierTypeId) {
            DB::table('t_ThirdPartyType_ThirdParties')->insert([
                'TypeId' => $supplierTypeId,
                'ThirdPartyId' => $supplier->Id,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }
        $supplier->categories()->sync($request->input('category_ids', []));

        // Auto-submit to workflow
        $supplierMaster = SupplierMaster::where('ThirdPartyId', $supplier->Id)->first();
        if ($supplierMaster) {
            try {
                $this->workflowService->submit($supplierMaster, actor: Auth::user(), remarks: 'Submitted ');
            } catch (\Exception $e) {
                // Log error but allow creation to succeed, specific error handling dependent on requirements
                \Log::error('Failed to submit supplier for approval: ' . $e->getMessage());
            }
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(ThirdParties $supplier)
    {
        $supplier->load('categories', 'types');

        return view('procurement.suppliers.show', compact('supplier'));
    }

    public function edit(ThirdParties $supplier)
    {
        $supplier->load('categories', 'types');
        $countries = \App\Models\Core\Country::all();
        $businessTypes = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'BusinessType')->get();
        $supplierCategories = \App\Models\ThirdParty\SupplierCategory::all();

        return view('procurement.suppliers.edit', compact('supplier', 'countries', 'businessTypes', 'supplierCategories'));
    }

    public function update(UpdateSupplierRequest $request, ThirdParties $supplier)
    {
        $validatedData = $request->validated();
        $userId = Auth::id();

        // 1. Update ThirdParties (Company Details)
        // explicitly filter fields to avoid errors and ensure safety
        $partyData = collect($validatedData)->only([
            'ThirdPartyName',
            'TradingName',
            'RegistrationNumber',
            'TaxPIN',
            'VATNumber',
            'PhysicalAddress',
            'Email',
            'Phone',
            'Website',
        ])->toArray();

        $partyData['ModifiedBy'] = $userId;

        // Map Country input to CountryId
        if ($request->has('Country')) {
            $partyData['CountryId'] = $request->input('Country');
        }

        // Handle BusinessType mapping if necessary (assuming value passed matches expected storage, or key)
        if ($request->has('BusinessType')) {
            $partyData['BusinessType'] = $request->input('BusinessType');
        }

        $supplier->update($partyData);

        // 2. Update SupplierMaster (Status, Prequalification)
        $supplierMaster = SupplierMaster::where('ThirdPartyId', $supplier->Id)->first();
        if ($supplierMaster) {
            // We only update ApprovalStatus. IsPrequalified is read-only/managed otherwise.
            $masterData = collect($validatedData)->only(['ApprovalStatus'])->toArray();

            // Handle Suspended Toggle Logic
            if ($request->boolean('Suspended')) {
                $masterData['ApprovalStatus'] = ThirdPartyApprovalStatusEnum::Suspended;
                // Also deactivate associated ThirdPartyUsers when suspended
                \App\Models\ThirdParty\ThirdPartyUser::where('ThirdPartyId', $supplier->Id)
                    ->update(['IsActive' => false]);
            } elseif ($request->input('ApprovalStatus') === ThirdPartyApprovalStatusEnum::Suspended->value) {
                // If switch was turned off, default back to Approved or Pending (A or P)
                $masterData['ApprovalStatus'] = ThirdPartyApprovalStatusEnum::Approved;
                // Reactivate associated ThirdPartyUsers when unsuspended
                \App\Models\ThirdParty\ThirdPartyUser::where('ThirdPartyId', $supplier->Id)
                    ->update(['IsActive' => true]);
            }

            $masterData['ModifiedBy'] = $userId;
            $supplierMaster->update($masterData);
        }

        // 3. Sync Categories
        // Categories are linked to ThirdParties (or SupplierMaster? Controller index used SupplierMaster->categories)
        // Check relationships. SupplierMaster has categories(). ThirdParties has categories().
        // Existing code syncs on $supplier (ThirdParties).
        // Let's ensure consistency. If SupplierMaster is the main entity for procurement, maybe sync there?
        // But ThirdParties::categories() maps to t_ThirdParty_SupplierCategory.
        // SupplierMaster::categories() maps to the SAME table.
        // So updating either should work. I'll stick to $supplier to minimize change impact unless directed.
        $supplier->categories()->sync($request->input('category_ids', []));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(ThirdParties $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    public function submit($id)
    {
        $supplier = SupplierMaster::where('ThirdPartyId', $id)->firstOrFail();

        try {
            $this->workflowService->submit($supplier, Auth::user());

            return redirect()->back()->with('success', 'Supplier submitted for approval.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Submission failed: ' . $e->getMessage());
        }
    }

    public function activate($id)
    {
        $supplier = SupplierMaster::where('ThirdPartyId', $id)->firstOrFail();

        // Use Workflow Service to approve
        try {
            if ($this->workflowService->canApproveSupplier($supplier, Auth::user())) {
                $this->workflowService->approve($supplier, Auth::user());

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($supplier)
                    ->event('approve')
                    ->log("Approved supplier {$supplier->SupplierID} linked to ThirdParty {$id}");

                return redirect()->back()->with('success', 'Supplier approved successfully.');
            } else {
                $msg = $this->workflowService->getApprovalDetailsMessage(SupplierMaster::getPrimaryKey(), $supplier->SupplierID);

                return redirect()->back()->with('error', "You are not authorized to approve. " . $msg);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    public function suspend($id)
    {
        $supplier = SupplierMaster::where('ThirdPartyId', $id)->firstOrFail();

        // Check permission - using update policy for now as suspension is an edit
        if (!auth()->user()->can('update', $supplier)) {
            return redirect()->back()->with('error', 'You are not authorized to suspend this supplier.');
        }

        try {
            $supplier->ApprovalStatus = ThirdPartyApprovalStatusEnum::Suspended;
            
            // Capture user ID for modification tracking if available in model
            if (in_array('ModifiedBy', $supplier->getFillable())) {
                $supplier->ModifiedBy = Auth::id();
            }

            $supplier->save();

            // Deactivate associated users
            \App\Models\ThirdParty\ThirdPartyUser::where('ThirdPartyId', $id)
                ->update(['IsActive' => false]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($supplier)
                ->event('suspend')
                ->log("Suspended supplier {$supplier->SupplierID} linked to ThirdParty {$id}");

            return redirect()->back()->with('success', 'Supplier has been suspended.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Suspension failed: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $supplier = SupplierMaster::where('ThirdPartyId', $id)->firstOrFail();

        try {
            if ($this->workflowService->canApproveSupplier($supplier, Auth::user())) {
                // Logic to capture reject reason? For now, generic.
                $this->workflowService->reject($supplier, Auth::user(), 'Rejected from List');

                return redirect()->back()->with('success', 'Supplier rejected.');
            } else {
                return redirect()->back()->with('error', 'Authentication failed');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    private function getActionsButtons(SupplierMaster $supplier): string
    {
        // Use ThirdPartyId for the routes to match Resource controller expectations
        $id = $supplier->ThirdPartyId;

        $viewUrl = route('suppliers.show', $id);
        $editUrl = route('suppliers.edit', $id);
        $deleteUrl = route('suppliers.destroy', $id);
        $activateUrl = route('suppliers.activate', $id);
        $submitUrl = route('suppliers.submit', $id);
        $rejectUrl = route('suppliers.reject', $id);
        $suspendUrl = route('suppliers.suspend', $id);


        $buttons = '';

        if (auth()->user()->can('view', $supplier)) {
            $buttons .= '<a href="' . $viewUrl . '" class="btn btn-sm btn-info">View</a>';
        }

        if (auth()->user()->can('update', $supplier)) {
            $buttons .= '<a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a>';
        }

        if (auth()->user()->can('delete', $supplier)) {
            $buttons .= '
                <form action="' . $deleteUrl . '" method="POST" class="inline-block">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-danger delete-btn">Delete</button>
                </form>';
        }

        // Check workflow permission instead of generic policy
        $canApprove = $this->workflowService->canApproveSupplier($supplier, auth()->user());
        // Also check if status is not already Approved (Status A)
        $isApproved = false;
        if (is_object($supplier->ApprovalStatus) && property_exists($supplier->ApprovalStatus, 'value')) {
            $isApproved = $supplier->ApprovalStatus->value === ThirdPartyApprovalStatusEnum::Approved->value;
        } elseif (is_string($supplier->ApprovalStatus)) {
            $isApproved = $supplier->ApprovalStatus === 'A';
        }

        // Check if workflow has started
        $hasWorkflow = $supplier->workflowHistory()->exists();

        // Check if status is Submitted
        $isSubmitted = false;
        if (is_object($supplier->ApprovalStatus) && property_exists($supplier->ApprovalStatus, 'value')) {
            $isSubmitted = $supplier->ApprovalStatus->value === ThirdPartyApprovalStatusEnum::Submitted->value;
        } elseif (is_string($supplier->ApprovalStatus)) {
            $isSubmitted = $supplier->ApprovalStatus === ThirdPartyApprovalStatusEnum::Submitted->value; // 'U'
        }

        // Submit Button: If not approved, not submitted, and no active workflow (redundant if checking submitted)
        if (! $isApproved && ! $isSubmitted && ! $hasWorkflow) {
            $buttons .= '
                <form action="' . $submitUrl . '" method="POST" class="inline-block ms-1">
                    ' . csrf_field() . '
                    <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                </form>';
        }

        if (! $isApproved && $canApprove) {
            $buttons .= '
                <form action="' . $activateUrl . '" method="POST" class="inline-block ms-1">
                    ' . csrf_field() . '
                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                </form>';
            $buttons .= '
                <form action="' . $rejectUrl . '" method="POST" class="inline-block ms-1">
                    ' . csrf_field() . '
                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                </form>';
        }

        // Suspend Button: Show if NOT already suspended and user can update
        $isSuspended = false;
        if (is_object($supplier->ApprovalStatus) && property_exists($supplier->ApprovalStatus, 'value')) {
            $isSuspended = $supplier->ApprovalStatus->value === ThirdPartyApprovalStatusEnum::Suspended->value;
        } elseif (is_string($supplier->ApprovalStatus)) {
            $isSuspended = $supplier->ApprovalStatus === ThirdPartyApprovalStatusEnum::Suspended->value; // or check string 'Suspended'
        }

        if (!$isSuspended && auth()->user()->can('update', $supplier)) {
             $buttons .= '
                <form action="' . $suspendUrl . '" method="POST" class="inline-block ms-1" onsubmit="return confirm(\'Are you sure you want to suspend this supplier? Users will be deactivated.\');">
                    ' . csrf_field() . '
                    <button type="submit" class="btn btn-sm btn-dark">Suspend</button>
                </form>';
        }

        return $buttons;
    }
}
