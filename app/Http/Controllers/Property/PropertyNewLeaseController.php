<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyNewLeaseRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\TenantAndLease\PropertyNewLeaseService;
use DateTime;

class PropertyNewLeaseController extends Controller
{
    protected $service;

    public function __construct(PropertyNewLeaseService $service)
    {
        $this->service = $service;
    }
    //
    public function index()
    {
        $newleases = PropertyNewLease::with('tenant', 'property')->get();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.index', compact('newleases'));
    }

    public function create(){
      //  $this->authorize(PermissionEnum::PropertyNewLeaseCreate, PropertyNewLease::class);
        $properties = PropertyRegistry::where('IsActive', 1)
            ->whereHas('getBlockByProperty.floor.units', function ($query) {
                $query->where('IsRentable', 1)
                    ->where('CurrentStatus', 1);
            })
            ->with([
                'getBlockByProperty.floor.units' => function ($query) {
                    $query->where('IsRentable', 1)
                        ->where('CurrentStatus', 1);
                }
            ])->get();


        $newtenants = PropertyNewTenant::where('IsActive', true)->get();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.create', compact('newtenants', 'properties', 'codes'));
    }

    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)
            ->whereHas('floor.units', function ($query) {
                $query->where('IsRentable', 1)
                    ->where('CurrentStatus', 1);
            })
            ->with([
                'floor.units' => function ($query) {
                    $query->where('IsRentable', 1)
                        ->where('CurrentStatus', 1);
                }
            ])
            ->get();

        return response()->json($blocks);
    }

    public function getFloorByBlock($blockId)
    {
        $floors = PropertyFloor::where('BlockID', $blockId)
            ->whereHas('units', function ($query) {
                $query->where('IsRentable', 1)
                    ->where('CurrentStatus', 1);
            })
            ->with([
                'units' => function ($query) {
                    $query->where('IsRentable', 1)
                        ->where('CurrentStatus', 1);
                }
            ])
            ->get();

        return response()->json($floors);
    }

    public function getUnitByFloor($floorId)
    {
        $units = PropertyUnit::where('FloorId', $floorId)
            ->where('IsRentable', 1)
            ->where('CurrentStatus', 1)
            ->get();

        return response()->json($units);
    }


    public function show($Id)
    {
       // $this->authorize(PermissionEnum::PropertyNewLeaseView, PropertyNewLease::class);
        $newlease = PropertyNewLease::where('isActive', true)->findOrFail($Id);
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.show', compact('newlease'));
    }

    public function store(PropertyNewLeaseRequest $request)
    {

      //  $this->authorize(PermissionEnum::PropertyNewLeaseCreate, PropertyNewLease::class);
        $data = $request->validated();
        $tenant = PropertyNewTenant::findOrFail($data['Tenant']);
        $property = PropertyRegistry::findOrFail($data['PropertyID']);
        $block = PropertyBlock::findOrFail($data['BlockID']);
        $floor = PropertyFloor::findOrFail($data['FloorID']);
        $unit = PropertyUnit::findOrFail($data['Unit']);
        $paymentFrequency = CodeDetail::findOrFail($data['PaymentFrequency']);
        foreach ($request->file('Document', []) as $uploadedFile) {
        $this->service::create(
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
            $data['DueDay'],
            $data['SpecialTerms'] ?? '',
            $request->user(),
            $uploadedFile
        );
    }
        return redirect()->route('addlease.index')->with('success', 'Lease created successfully');
    }

    public function edit($Id)
    {
      //  $this->authorize(PermissionEnum::PropertyNewLeaseUpdate, PropertyNewLease::class);
        $newlease = PropertyNewLease::where('isActive', true)->findOrFail($Id);
        $properties = PropertyRegistry::with('getBlockByProperty.floor.units')->get();
        $newtenants = PropertyNewLease::with('tenant')->get();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.edit', compact(
            'newlease', 'newtenants', 'properties', 'codes'
        ));
    }

    public function update(PropertyNewLeaseRequest $request, $Id)
    {
       // $this->authorize(PermissionEnum::PropertyNewLeaseUpdate, PropertyNewLease::class);
        $data = $request->validated();

        $lease = PropertyNewLease::findOrFail($Id);

        $property = PropertyRegistry::findOrFail($data['PropertyID']);
        $block = PropertyBlock::findOrFail($data['BlockID']);
        $floor = PropertyFloor::findOrFail($data['FloorID']);
        $unit = PropertyUnit::findOrFail($data['Unit']);
        $frequency = CodeDetail::findOrFail($data['PaymentFrequency']);
        $user = auth()->user();

        $this->service::update(
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
            user: $user
        );

        foreach ($request->file('Document', []) as $uploadedFile) {
        $this->service::update(
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
            document: $uploadedFile
        );
    }

        return redirect()->route('addlease.index')->with('success', 'Lease updated successfully.');
    }


    public function destroy($Id)
    {
      //  $this->authorize(PermissionEnum::PropertyNewLeaseDelete, PropertyNewLease::class);
        $newlease = PropertyNewLease::findOrFail($Id);

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
