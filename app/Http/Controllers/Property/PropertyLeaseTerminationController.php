<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyLeaseTerminationRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Property\TenantAndLease\PropertyLeaseTerminationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PropertyLeaseTerminationController extends Controller
{
    protected $service;

    public function __construct(PropertyLeaseTerminationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $leaseterminations = PropertyLeaseTermination::with('lease', 'lease.tenant', 'code')->get();

        return view('property.tenantmanagement.leasemanagement.leasetermination.index', compact('leaseterminations'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyLeaseTerminationCreate, PropertyLeaseTermination::class);
        $newtenants = PropertyNewLease::where('IsActive', true)->get();
        $terminationReasons = CodeDetail::where('CodeID', 'TerminationReason')->get();

        return view('property.tenantmanagement.leasemanagement.leasetermination.create', compact('newtenants', 'terminationReasons'));
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseTerminationView, PropertyLeaseTermination::class);
        $leasetermination = PropertyLeaseTermination::with('lease', 'lease.tenant', 'code')->findOrFail($Id);

        return view('property.tenantmanagement.leasemanagement.leasetermination.show', compact('leasetermination'));
    }

    public function store(PropertyLeaseTerminationRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyLeaseTerminationCreate, PropertyLeaseTermination::class);
        $validatedData = $request->validated();
        $LeaseID = PropertyNewLease::findOrFail($validatedData['LeaseID']);
        $TerminationReason = CodeDetail::findOrFail($validatedData['TerminationReason']);
        foreach ($request->file('Document', []) as $uploadedFile) {
            $this->service->create(
                $LeaseID,
                $TerminationDate = $validatedData['TerminationDate'],
                $TerminationReason,
                $Remarks = $validatedData['Remarks'] ?? '',
                ApprovalEnum::Pending->value,
                Auth::user(),
                $uploadedFile
            );
        }

        return redirect()->route('terminatelease.index')->with('success', 'Lease termination created successfully');
    }

    public function leaseTerminationLetter($Id)
    {
        $this->authorize(
            PermissionEnum::PropertyLeaseTerminationView,
            PropertyLeaseTermination::class
        );

        $termination = PropertyLeaseTermination::with([
            'lease',
            'lease.tenant.thirdParty',
            'lease.property',
            'lease.block',
            'lease.floor',
            'lease.unit',
            'code',
        ])->findOrFail($Id);

        // Log event
        activity()
            ->causedBy(auth()->user()->Id)
            ->performedOn($termination)
            ->event('termination_letter_generated')
            ->log("Generated Lease Termination Letter for Lease #{$termination->lease->LeaseNumber}");

        // Generate PDF
        $pdf = Pdf::loadView(
            'property.tenantmanagement.leasemanagement.leasetermination.TerminationLetter',
            compact('termination')
        )->output();

        // Save document
        $termination->newDocumentFromContent(
            module: ModulesEnum::Property,
            extension: ExtensionsEnum::Pdf,
            fileName: "Lease_Termination_{$termination->lease->LeaseNumber}.pdf",
            content: $pdf,
            actor: auth()->user(),
            permissions: [PermissionEnum::PropertyLeaseTerminationView->value]
        );

        return redirect()
            ->route('terminatelease.index')
            ->with('success', 'Lease Termination Letter generated successfully.');
    }
}
