<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyLeaseRenewalRequest;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Property\TenantAndLease\PropertyLeaseRenewalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PropertyLeaseRenewalController extends Controller
{
    public function index()
    {
        $leaserenewals = PropertyLeaseRenewal::with(['lease.tenant', 'lease.property'])
            ->where('isActive', true)->get();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.index', compact('leaserenewals'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalCreate, PropertyLeaseRenewal::class);
        $newleases = PropertyNewLease::with('tenant', 'property')
            ->where('isActive', true)
            ->where('Status', '!=', PropertyNewLeaseEnum::Terminate)
            ->get();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.create', compact('newleases'));
    }

    public function getPropertyByTenant($tenantId)
    {
        $newlease = PropertyNewLease::where('TenantId', $tenantId)->get();
        return response()->json($newlease);
    }

    public function getLeaseByProperty($propertyId)
    {
        $newlease = PropertyNewLease::where('PropertyId', $propertyId)->get();
        return response()->json($newlease);
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalView, PropertyLeaseRenewal::class);
        $leaserenewal = PropertyLeaseRenewal::findOrFail($id);
        return view('property.tenantmanagement.leasemanagement.leaserenewal.show', compact('leaserenewal'));
    }

public function store(PropertyLeaseRenewalRequest $request)
{
    $this->authorize(PermissionEnum::PropertyLeaseRenewalCreate, PropertyLeaseRenewal::class);

    DB::beginTransaction();
    try {
        $validated = $request->validated();
        $leaseId = (int)$validated['LeaseId'];
        $paymentFrequencyId = (int)$validated['PaymentFrequency'];

        // Create the lease renewal without the uploaded file for now
        $leaseRenewal = PropertyLeaseRenewalService::create(
            $leaseId,
            $paymentFrequencyId,
            $validated['EndDateCurrentLease'],
            $validated['NewStartDate'],
            $validated['NewEndDate'],
            $validated['NewMonthlyRent'],
            $validated['ServiceCharge'],
            $validated['ParkingFee'],
            $validated['OtherCharges'],
            $validated['Remarks'] ?? '',
            ApprovalEnum::Pending->value,
            Auth::user()
        );

        // Optional: handle uploaded documents
        foreach ($request->file('Document', []) as $uploadedFile) {
            $leaseRenewal->newDocument(
                 ModulesEnum::Property,
                 $uploadedFile,
                 [PermissionEnum::PropertyLeaseRenewalView->value],
                 Auth::user()
            );
        }

        DB::commit();
        return redirect()->route('renewlease.index')->with('success', 'Lease renewal created successfully');

    } catch (Throwable $e) {
        DB::rollBack();
        Log::error('Lease Renewal creation failed: ' . $e->getMessage());
        return back()->with('error', $e->getMessage())->withInput();
    }
}

    public function leaseRenewalOfferLetter($Id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalView, PropertyLeaseRenewal::class);

        $leaseRenewal = PropertyLeaseRenewal::with([
            'lease.tenant.thirdParty',
            'lease.property',
            'lease.block',
            'lease.floor',
            'lease.unit',
            'lease.code'
        ])->findOrFail($Id);

        activity()
            ->causedBy(Auth::user()->Id)
            ->performedOn($leaseRenewal)
            ->event('renewal_offer_letter_generated')
            ->log("Generated Lease Renewal Offer Letter for Lease #{$leaseRenewal->lease->LeaseNumber}");

        // Generate PDF
        $pdf = Pdf::loadView(
            'property.tenantmanagement.leasemanagement.leaserenewal.renewalofferletter',
            compact('leaseRenewal')
        )->output();

        $leaseRenewal->lease->newDocumentFromContent(
            module: ModulesEnum::Property,
            extension: ExtensionsEnum::Pdf,
            fileName: "Lease_Renewal_Offer_{$leaseRenewal->lease->LeaseNumber}.pdf",
            content: $pdf,
            actor: Auth::user(),
            permissions: [PermissionEnum::PropertyLeaseRenewalView->value]
        );

        return redirect()->route('renewlease.index')
            ->with('success', 'Lease Renewal Offer Letter generated successfully.');
    }

    public function edit($Id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalUpdate, PropertyLeaseRenewal::class);
        $leaserenewal = PropertyLeaseRenewal::where('isActive', true)->findOrFail($Id);
        $newleases = PropertyNewLease::with('tenant', 'property')->get();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.edit', compact('leaserenewal', 'newleases'));
    }

    public function update(PropertyLeaseRenewalRequest $request, $Id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalUpdate, PropertyLeaseRenewal::class);
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $leaseRenewal = PropertyLeaseRenewal::findOrFail($Id);

            PropertyLeaseRenewalService::update(
                $leaseRenewal,
                $validated['LeaseId'],
                $validated['PaymentFrequency'],
                $validated['EndDateCurrentLease'],
                $validated['NewStartDate'],
                $validated['NewEndDate'],
                $validated['NewMonthlyRent'],
                $validated['ServiceCharge'] ?? 0,
                $validated['ParkingFee'] ?? 0,
                $validated['OtherCharges'] ?? 0,
                $validated['Remarks'] ?? '',
                Auth::user()
            );

            // Regenerate PDF after update
            $leaseRenewal->load(['lease', 'lease.tenant']);
            $pdf = Pdf::loadView(
                'property.tenantmanagement.leasemanagement.leaserenewal.renewalofferletter',
                compact('leaseRenewal')
            )->output();

            $leaseRenewal->lease->newDocumentFromContent(
                module: ModulesEnum::Property,
                extension: ExtensionsEnum::Pdf,
                fileName: "Lease_Renewal_Offer_{$leaseRenewal->lease->LeaseNumber}.pdf",
                content: $pdf,
                actor: Auth::user(),
                permissions: [PermissionEnum::PropertyLeaseRenewalView->value]
            );

            DB::commit();
            return redirect()->route('renewlease.index')->with('success', 'Lease renewal updated successfully');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Lease Renewal: ' . $th->getMessage());
            return back()->withErrors(['error' => 'Failed to update Lease Renewal'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalDelete, PropertyLeaseRenewal::class);

        try {
            $leaseRenewal = PropertyLeaseRenewal::findOrFail($id);
            PropertyLeaseRenewalService::delete($leaseRenewal, Auth::user());
            return redirect()->route('renewlease.index')->with('success', 'Lease Renewal soft-deleted successfully!');
        } catch (Throwable $th) {
            Log::error('Error soft-deleting Lease Renewal: ' . $th->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete Lease Renewal. Please try again.'])->withInput();
        }
    }
}
