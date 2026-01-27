<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyNewLeaseRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRateAndPricing;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\TenantAndLease\PropertyNewLeaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;

class PropertyNewLeaseController extends Controller
{
    protected $service;

    public function __construct(PropertyNewLeaseService $service)
    {
        $this->service = $service;
    }


    public function index()
    {
        $this->authorize(PermissionEnum::PropertyNewLeaseView, PropertyNewLease::class);
        $newleases = PropertyNewLease::with(['tenant', 'property'])->get();

        if (request()->wantsJson()) {
            return response()->json($newleases);
        }

        return view('property.tenantmanagement.leasemanagement.leasemaintenance.index', compact('newleases'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyNewLeaseCreate, PropertyNewLease::class);
        $properties = PropertyRegistry::where('IsActive', true)
            ->whereHas('getBlockByProperty.floor.units', function ($query) {
                $query->where('IsRentable', true)
                    ->where('CurrentStatus', true);
            })
            ->with([
                'getBlockByProperty.floor.units' => function ($query) {
                    $query->where('IsRentable', true)
                        ->where('CurrentStatus', true);
                },
            ])->get();
        $Currencies = Currency::all();
        $taxtypes = FinanceTaxRuleConfiguration::with('taxType')->get();


        $newtenants = PropertyNewTenant::where('IsActive', true)->get();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('property.tenantmanagement.leasemanagement.leasemaintenance.create', compact('newtenants', 'properties', 'codes', 'taxtypes', 'Currencies'));
    }

    public function getPricingUnit($UnitId)
    {
        $pricing = PropertyRateAndPricing::where('UnitId', $UnitId)->first();

        if (! $pricing) {
            return response()->json(null, 200);
        }

        return response()->json([
            'Rent' => $pricing->Rent,
            'DepositAmount' => $pricing->DepositAmount,
            'ServiceCharge' => $pricing->ServiceCharge,
            'ParkingFee' => $pricing->ParkingFee,
            'OtherCharges' => $pricing->OtherCharges,
            'TaxId' => $pricing->TaxId,
            'CurrencyId' => $pricing->CurrencyId,
        ]);
    }

    public function getBlockByProperty($PropertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $PropertyId)
            ->whereHas('floor.units', function ($query) {
                $query->where('IsRentable', true)
                    ->where('CurrentStatus', true);
            })
            ->with([
                'floor.units' => function ($query) {
                    $query->where('IsRentable', true)
                        ->where('CurrentStatus', true);
                },
            ])
            ->get();

        return response()->json($blocks);
    }

    public function getFloorByBlock($BlockId)
    {
        $floors = PropertyFloor::where('BlockID', $BlockId)
            ->whereHas('units', function ($query) {
                $query->where('IsRentable', true)
                    ->where('CurrentStatus', true);
            })
            ->with([
                'units' => function ($query) {
                    $query->where('IsRentable', true)
                        ->where('CurrentStatus', true);
                },
            ])
            ->get();

        return response()->json($floors);
    }

    public function getUnitByFloor($FloorId)
    {
        $units = PropertyUnit::where('FloorId', $FloorId)
            ->where('IsRentable', true)
            ->where('CurrentStatus', true)
            ->get();

        return response()->json($units);
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyNewLeaseView, PropertyNewLease::class);
        $newlease = PropertyNewLease::where('isActive', true)->findOrFail($Id);

        return view('property.tenantmanagement.leasemanagement.leasemaintenance.show', compact('newlease'));
    }

    public function store(PropertyNewLeaseRequest $request)
    {

        $this->authorize(PermissionEnum::PropertyNewLeaseCreate, PropertyNewLease::class);
        $data = $request->validated();
        $tenant = PropertyNewTenant::findOrFail($data['Tenant']);
        $property = PropertyRegistry::findOrFail($data['PropertyID']);
        $block = PropertyBlock::findOrFail($data['BlockID']);
        $floor = PropertyFloor::findOrFail($data['FloorID']);
        $unit = PropertyUnit::findOrFail($data['Unit']);
        $paymentFrequency = CodeDetail::findOrFail($data['PaymentFrequency']);
        $CurrencyId = Currency::findOrFail($data['CurrencyId']);
        $TaxId = FinanceTaxRuleConfiguration::findOrFail($data['TaxId']);

        foreach ($request->file('Document', []) as $uploadedFile) {
            $this->service->create(
                $tenant,
                $property,
                $block,
                $floor,
                $unit,
                new DateTime($data['StartDate']),
                new DateTime($data['EndDate']),
                $paymentFrequency,
                $data['MonthlyRent'],
                $data['Deposit'],
                $data['ServiceCharge'],
                $data['ParkingFee'],
                $data['OtherCharges'],
                PropertyNewLeaseEnum::OfferLetter->value,
                ApprovalEnum::Pending->value,
                false,
                $data['DueDay'],
                $data['SpecialTerms'] ?? '',
                $request->user(),
                $CurrencyId,
                $TaxId,
                $uploadedFile
            );
        }

        return redirect()->route('addlease.index')->with('success', 'Lease created successfully');
    }

    public function edit($Id)
    {
        $this->authorize(PermissionEnum::PropertyNewLeaseUpdate, PropertyNewLease::class);
        $newlease = PropertyNewLease::findOrFail($Id);
        $properties = PropertyRegistry::with('getBlockByProperty.floor.units')->get();
        $newtenants = PropertyNewLease::with('tenant')->get();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();
        $Currencies = Currency::all();
        $taxtypes = FinanceTaxRuleConfiguration::with('taxType')->get();

        return view('property.tenantmanagement.leasemanagement.leasemaintenance.edit', compact(
            'newlease',
            'newtenants',
            'properties',
            'codes',
            'taxtypes',
            'Currencies'
        ));
    }

    public function update(PropertyNewLeaseRequest $request, $Id)
    {
        $this->authorize(PermissionEnum::PropertyNewLeaseUpdate, PropertyNewLease::class);
        $data = $request->validated();

        $lease = PropertyNewLease::findOrFail($Id);

        $property = PropertyRegistry::findOrFail($data['PropertyID']);
        $block = PropertyBlock::findOrFail($data['BlockID']);
        $floor = PropertyFloor::findOrFail($data['FloorID']);
        $unit = PropertyUnit::findOrFail($data['Unit']);
        $frequency = CodeDetail::findOrFail($data['PaymentFrequency']);
        $CurrencyId = Currency::findOrFail($data['CurrencyId']);
        $TaxId = FinanceTaxRuleConfiguration::findOrFail($data['TaxId']);
        $user = auth()->user();

        $this->service->update(
            lease: $lease,
            PropertyID: $property,
            BlockID: $block,
            FloorID: $floor,
            Unit: $unit,
            StartDate: new \DateTime($data['StartDate']),
            EndDate: new \DateTime($data['EndDate']),
            PaymentFrequency: $frequency,
            MonthlyRent: (float)$data['MonthlyRent'],
            Deposit: (float)$data['Deposit'],
            ServiceCharge: (float)$data['ServiceCharge'],
            ParkingFee: (float)$data['ParkingFee'],
            OtherCharges: (float)$data['OtherCharges'],
            DueDay: (int)$data['DueDay'],
            SpecialTerms: $data['SpecialTerms'] ?? '',
            user: $user,
            CurrencyId: $CurrencyId,
            TaxId: $TaxId,
        );

        foreach ($request->file('Document', []) as $uploadedFile) {
            $this->service->update(
                lease: $lease,
                PropertyID: $property,
                BlockID: $block,
                FloorID: $floor,
                Unit: $unit,
                StartDate: new \DateTime($data['StartDate']),
                EndDate: new \DateTime($data['EndDate']),
                PaymentFrequency: $frequency,
                MonthlyRent: (float)$data['MonthlyRent'],
                Deposit: (float)$data['Deposit'],
                ServiceCharge: (float)$data['ServiceCharge'],
                ParkingFee: (float)$data['ParkingFee'],
                OtherCharges: (float)$data['OtherCharges'],
                DueDay: (int)$data['DueDay'],
                SpecialTerms: $data['SpecialTerms'] ?? '',
                user: $user,
                CurrencyId: $CurrencyId,
                TaxId: $TaxId,
                document: $uploadedFile
            );
        }

        return redirect()->route('addlease.index')->with('success', 'Lease updated successfully.');
    }

    public function leaseOfferLetter($Id)
    {
        $lease = PropertyNewLease::with(['tenant.thirdParty', 'property', 'block', 'floor', 'unit', 'code', 'currency'])->findOrFail($Id);

        $lease->update([
            'IsOfferGenerated' => true,
            'ModifiedBy' => auth()->user()->Id,
            'ModifiedOn' => now(),
        ]);

        activity()
            ->causedBy(auth()->user()->Id)
            ->performedOn($lease)
            ->event('offer_generated')
            ->log("Generated Lease Offer Letter for {$lease->LeaseNumber}.");

        $pdf = Pdf::loadView('property.tenantmanagement.leasemanagement.leasemaintenance.Offerletter', compact('lease'))->output();

        $lease->newDocumentFromContent(
            module: ModulesEnum::Property,
            extension: ExtensionsEnum::Pdf,
            fileName: "Lease_Offer_{$lease->LeaseNumber}.pdf",
            content: $pdf,
            actor: auth()->user(),
            permissions: [PermissionEnum::PropertyNewLeaseView->value]
        );

        return redirect()->route('addlease.index')->with('success', 'Lease Offer Letter generated successfully.');
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::PropertyNewLeaseDelete, PropertyNewLease::class);
        $newlease = PropertyNewLease::findOrFail($Id);

        if ($newlease->invoices()->exists()) {
            return redirect()->back()
                ->withErrors(['error' => 'This lease is in use and cannot be deleted.']);
        }

        PropertyLeaseSchedule::where('LeaseNumber', $newlease->Id)->delete();
        $newlease->delete();

        activity()
            ->causedBy(auth()->user()->Id)
            ->performedOn($newlease)
            ->event('delete')
            ->log("Deleted Lease {$newlease->Id}.");

        return redirect()->route('addlease.index')->with('success', 'Lease deleted successfully.');
    }
}
