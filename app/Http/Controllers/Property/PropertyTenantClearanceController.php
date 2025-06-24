<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyTenantClearanceRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyTenantClearance;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Services\Property\TenantAndLease\PropertyTenantClearanceService;

class PropertyTenantClearanceController extends Controller
{

    protected $service;

    public function __construct(PropertyTenantClearanceService $service)
    {
        $this->service = $service;
    }
    //
    public function index()
    {
        $clearancetenants = PropertyTenantClearance::all();
        return view('property.tenantmanagement.tenantclearance.index', compact('clearancetenants'));
    }

    public function create(){
        $this->authorize(PermissionEnum::TenantMentenanceCreate, PropertyTenantClearance::class);
        $newtenants = PropertyNewTenant::where('IsActive', true)->get();
        $codedetails = CodeDetail::where('CodeID', 'DepositRefunded')->get();
        return view('property.tenantmanagement.tenantclearance.create', compact('newtenants', 'codedetails'));
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::TenantMentenanceView, PropertyTenantClearance::class);
        $clearancetenant = PropertyTenantClearance::find($Id);
        return view('property.tenantmanagement.tenantclearance.show', compact('clearancetenant'));
    }

    public function store(PropertyTenantClearanceRequest $request)
    {
        $this->authorize(PermissionEnum::TenantMentenanceCreate, PropertyTenantClearance::class);
        $validatedData = $request->validated();
        $tenant = PropertyNewTenant::findOrFail($validatedData['Tenant']);
        $depositRefunded = $validatedData['DepositRefunded'] ? CodeDetail::findOrFail($validatedData['DepositRefunded']) : null;
        $clearance = $this->service->create(
            $tenant,
            $validatedData['ExitDate'],
            $validatedData['FinalInspection'],
            $validatedData['AllDuesPaid'],
            $validatedData['KeysReturned'],
            $depositRefunded,
            $validatedData['AdditionalNotes'],
            $request->user()
        );
        return redirect()->route('tenantclearance.index')->with('success', 'Tenant created successfully');

    }
     public function edit($id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::TenantMentenanceUpdate, PropertyTenantClearance::class);
        $clearancetenant = PropertyTenantClearance::findOrFail($id);
        $codedetails = CodeDetail::where('CodeID', 'DepositRefunded')->get();
        $newtenants = PropertyNewTenant::where('IsActive', true)->get();
        return view('property.tenantmanagement.tenantclearance.edit', compact('clearancetenant', 'codedetails', 'newtenants'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::TenantMentenanceUpdate, PropertyTenantClearance::class);
        $validated = $request->validate([
            'Tenant' => 'required|exists:t_TenantMaintenance,Id',
            'ExitDate' => 'required|date',
            'FinalInspection' => 'required|boolean',
            'AllDuesPaid' => 'required|boolean',
            'KeysReturned' => 'required|boolean',
            'DepositRefunded' => 'required|exists:t_CodeDetails,Id',
            'AdditionalNotes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $clearancetenant = PropertyTenantClearance::findOrFail($id);

            $clearancetenant->update([
                'Tenant' => $validated['Tenant'],
                'ExitDate' => $validated['ExitDate'],
                'FinalInspection' => $validated['FinalInspection'],
                'AllDuesPaid' => $validated['AllDuesPaid'],
                'KeysReturned' => $validated['KeysReturned'],
                'DepositRefunded' => $validated['DepositRefunded'],
                'AdditionalNotes' => $validated['AdditionalNotes'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($clearancetenant)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Tenant Clearance');

            return redirect()->route('tenantclearance.index')->with('success', 'Tenant Clearance updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Tenant Clearance:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update Tenant Clearance'])->withInput();
        }
    }

    public function destroy($id)
    {
        //Check if user has permission to delete property categories
       $this->authorize(PermissionEnum::TenantMentenanceDelete, PropertyTenantClearance::class);
        try {
            $clearancetenant = PropertyTenantClearance::findOrFail($id);
            $clearancetenant->delete();

            return redirect()->route('tenantclearance.index')
                ->with('success', 'Tenant Clearance Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Tenant Clearance: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Tenant Clearance. Please try again.'])
                ->withInput();
        }
    }

}


