<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Enums\Insurance\InsuranceReferralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancAssuranceReferralRequest;
use App\Models\Core\Branch;
use App\Models\Auth\User;
use App\Models\HRM\Employee;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\BancAssuranceReferralService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BancassuranceReferralController extends Controller
{

    public function index()
    {
        $this->authorize(PermissionEnum::BancassuranceReferralView, BancAssuranceReferral::class);
        $referrals = BancAssuranceReferral::with(['insuranceProduct', 'preferredInsurer', 'assignedToUser',])
            ->orderByDesc('Id')->get();

        return view('bancassurance.referrals.index', compact('referrals'));
    }

    public function create()
    {
        //$this->authorize(PermissionEnum::BancassuranceReferralView, BancAssuranceReferral::class);
        $insurers = InsuranceProvider::all();
        $users = User::with('employee')->get();
        return view('bancassurance.referrals.create', compact('users', 'insurers'));
    }


    public function getProductsByInsurer($insurerId)
    {
        $products = InsuranceProduct::where('InsuranceProviderID', $insurerId)->get();
        return response()->json($products);
    }


    public function store(BancAssuranceReferralRequest $request)
    {
//$this->authorize(PermissionEnum::BancassuranceReferralCreate, BancAssuranceReferral::class);
        $user = auth()->user();
        $validated = $request->validated();

        // Handle nullable relationships safely
        $ReferredBy = !empty($validated['ReferredBy']) ? User::findOrFail($validated['ReferredBy']) : $user;

        $AssignedTo = !empty($validated['AssignedTo']) ? User::findOrFail($validated['AssignedTo']) : null;

        $InsuranceProductId = !empty($validated['InsuranceProductId']) ? InsuranceProduct::findOrFail($validated['InsuranceProductId']) : null;

        $PreferredInsurerId = InsuranceProvider::findOrFail($validated['PreferredInsurerId']);

        $branchId = $user->employee->BranchId ?? null;
        $BranchId = Branch::findOrFail($branchId);

        // Determine status based on assignment
        $Status = $AssignedTo ? InsuranceReferralStatus::Assigned : InsuranceReferralStatus::Pending;

        // Create the referral
        $assurancereferral = BancAssuranceReferralService::create(
            $validated['ClientName'],
            $validated['ClientIDNumber'],
            $validated['ClientPhone'],
            $validated['ClientEmail'],
            $ReferredBy,
            Carbon::parse($validated['ReferralDate']),
            $InsuranceProductId,
            $PreferredInsurerId,
            $validated['Remarks'],
            $Status,
            $AssignedTo,
            $BranchId,
            auth()->user()
        );

        return redirect()->route('bancassurance.referrals.index')
            ->with('success', 'Referral submitted!');
    }

    public function edit($Id)
    {
        $this->authorize(PermissionEnum::BancassuranceReferralUpdate, BancAssuranceReferral::class);
        $referral = BancAssuranceReferral::with(['insuranceProduct', 'preferredInsurer', 'assignedToUser'])->findOrFail($Id);
        $insuranceproducts = InsuranceProduct::all();
        $insurers = InsuranceProvider::all();
        $users = User::with('employee')->get();

        return view('bancassurance.referrals.edit', compact('referral', 'users', 'insurers', 'insuranceproducts'));
    }

    public function update(BancAssuranceReferralRequest $request, $Id)
    {
        $this->authorize(PermissionEnum::BancassuranceReferralUpdate, BancAssuranceReferral::class);
        $user = auth()->user();
        $validated = $request->validated();

        $referral = BancAssuranceReferral::where('Id', $Id)->firstOrFail();

        // Fetch model instances
        $ReferredBy = !empty($validated['ReferredBy']) ? User::findOrFail($validated['ReferredBy']) : $user;

        $AssignedTo = !empty($validated['AssignedTo']) ? User::findOrFail($validated['AssignedTo']) : null;

        $InsuranceProductId = !empty($validated['InsuranceProductId']) ? InsuranceProduct::findOrFail($validated['InsuranceProductId']) : null;

        $PreferredInsurerId = InsuranceProvider::findOrFail($validated['PreferredInsurerId']);

        $Status = $AssignedTo ? InsuranceReferralStatus::Assigned : InsuranceReferralStatus::Pending;

        $branchId = $user->employee->BranchId ?? null;
        $Branch = Branch::findOrFail($branchId);

        // Pass model instances to the service (not IDs)
        $referralupdate = BancAssuranceReferralService::update(
            $referral,
            $validated['ClientName'],
            $validated['ClientIDNumber'],
            $validated['ClientPhone'],
            $validated['ClientEmail'],
            $ReferredBy,
            Carbon::parse($validated['ReferralDate']),
            $InsuranceProductId,
            $PreferredInsurerId,
            $validated['Remarks'],
            $Status,
            $AssignedTo,
            $Branch,
            $user
        );

        return redirect()->route('bancassurance.referrals.index')
            ->with('success', 'Referral updated successfully!');
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::BancassuranceReferralView, BancAssuranceReferral::class);
        $referral = BancAssuranceReferral::with('employee')->findOrFail($Id);
        return view('bancassurance.referrals.show', compact('referral'));
    }

    public function assignList()
    {
        $this->authorize(PermissionEnum::BancassuranceReferralView, BancAssuranceReferral::class);
        $referrals = BancAssuranceReferral::with('insuranceProduct')
            ->whereNull('AssignedTo')
            ->where('Status', InsuranceReferralStatus::Pending->value) // Assuming 'P' stands for 'Pending'
            ->orderByDesc('Id')->get();

        $employees = Employee::whereNull('DeletedOn')
            ->select('Id', 'FirstName', 'LastName')
            ->get();

        return view('bancassurance.referrals.assign', compact('referrals', 'employees'));
    }


    public function assign(Request $request, $Id)
    {
        $this->authorize(PermissionEnum::BancassuranceReferralCreate, BancAssuranceReferral::class);
        $request->validate([
            'AssignedTo' => 'required|exists:t_Employees,Id',
        ]);

        $referral = BancAssuranceReferral::findOrFail($Id);

        $referral->AssignedTo = $request->AssignedTo;
        $referral->Status = InsuranceReferralStatus::Assigned->value;
        $referral->ModifiedOn = now();
        $referral->ModifiedBy = auth()->Id();

        $referral->save();

        return redirect()->back()->with('success', 'Referral assigned successfully.');
    }

    public function performanceView()
    {
        $this->authorize(PermissionEnum::BancassuranceReferralView, BancAssuranceReferral::class);
        $referrals = BancAssuranceReferral::with(['referredByEmployee.branch'])->get();

        $grouped = $referrals->groupBy(function ($referral) {
            return $referral->referredByEmployee?->Id ?? 'Unknown';
        });

        $performance = $grouped->map(function ($items) {
            $employee = $items->first()->referredByEmployee;
            $branch = $employee?->branch;

            return [
                'StaffName' => $employee ? $employee->FirstName . ' ' . $employee->LastName : 'Unknown',
                'BranchName' => $branch?->Name ?? 'Unknown',
                'Total' => $items->count(),
                'Converted' => $items->where('Status', InsuranceReferralStatus::Converted->value)->count(),
                'Pending' => $items->where('Status', InsuranceReferralStatus::Pending->value)->count(),
            ];
        })->sortByDesc('Total');

        return view('bancassurance.referrals.performance', ['performance' => $performance]);
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::BancassuranceReferralDelete, BancAssuranceReferral::class);
        $referral = BancAssuranceReferral::findOrFail($Id);
        $referral->DeletedBy = Auth()->Id();
        $referral->save();
        $referral->delete();

        activity()->causedBy(auth()->user()->Id)->performedOn($referral)
            ->event('delete')->log("Deleted Bank Assurance Referral {$referral->Id}.");

        return redirect()->route('bancassurance.referrals.index')->with('success', 'Referral deleted successfully!');

    }
}
