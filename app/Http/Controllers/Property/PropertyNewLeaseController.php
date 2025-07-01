<?php

namespace App\Http\Controllers\Property;

use App\Enums\Property\TenantClearanceEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyNewLeaseRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyTenantClearance;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\TenantAndLease\PropertyNewLeaseService;

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
        $properties = PropertyRegistry::with('getBlockByProperty.floor.units')->get();
        $newtenants = PropertyTenantClearance::with('tenant')->where('Status', TenantClearanceEnum::Cleared->value)->get();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.create', compact('newtenants', 'properties', 'codes'));
    }
    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)->get();
        //dd($blocks); // check if it's returning correctly
        return response()->json($blocks);
    }


    public function getFloorByBlock($blockId)
    {
        $floors = PropertyFloor::where('BlockID', $blockId)->get();
        return response()->json($floors);
    }
    public function getUnitByFloor($floorId)
    {
        $units = PropertyUnit::where('FloorId', $floorId)->get();
        return response()->json($units);
    }

    public function show($Id)
    {
        $newlease = PropertyNewLease::findOrFail($Id);
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.show', compact('newlease'));
    }

    public function store(PropertyNewLeaseRequest $request)
    {
    

        $data = $request->validated();
        $tenant = PropertyNewTenant::findOrFail($data['Tenant']);
        $property = PropertyRegistry::findOrFail($data['PropertyID']);
        $block = PropertyBlock::findOrFail($data['BlockID']);
        $floor = PropertyFloor::findOrFail($data['FloorID']);
        $unit = PropertyUnit::findOrFail($data['Unit']);
        $paymentFrequency = CodeDetail::findOrFail($data['PaymentFrequency']);
        $this->service::create(
            $tenant,
            $property,
            $block,
            $floor,
            $unit,
            $startDate = new \DateTime($data['StartDate']),
            $endDate = new \DateTime($data['EndDate']),
            $paymentFrequency,
            $monthlyRent = $data['MonthlyRent'],
            $deposit = $data['Deposit'],
            $dueDay = $data['DueDay'],
            $specialTerms = $data['SpecialTerms'],
            $request->user()
        );
        return redirect()->route('addlease.index')->with('success', 'Lease created successfully');
    }

    public function edit($Id)
    {
        $newlease = PropertyNewLease::findOrFail($Id);
        $properties = PropertyRegistry::with('getBlockByProperty.floor.units')->get();
        $newtenants = PropertyTenantClearance::with('tenant')->where('Status', TenantClearanceEnum::Cleared->value)->get();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.edit', compact(
            'newlease', 'newtenants', 'properties', 'codes'
        ));
    }

    public function update(PropertyNewLeaseRequest $request, $Id)
    {
        $data = $request->validated();

        $newlease = PropertyNewLease::findOrFail($Id);

        $newlease->update([
            'PropertyID' => $data['PropertyID'],
            'BlockID' => $data['BlockID'],
            'FloorID' => $data['FloorID'],
            'Unit' => $data['Unit'],
            'StartDate' => $data['StartDate'],
            'EndDate' => $data['EndDate'],
            'PaymentFrequency' => $data['PaymentFrequency'],
            'MonthlyRent' => $data['MonthlyRent'],
            'Deposit' => $data['Deposit'],
            'DueDay' => $data['DueDay'],
            'SpecialTerms' => $data['SpecialTerms'],
            'ModifiedBy' => $request->user()->Id,
        ]);

        activity()
            ->causedBy($request->user()->Id)
            ->performedOn($newlease)
            ->event('update')
            ->log("Updated Lease {$newlease->Id}.");

        return redirect()->route('addlease.index')->with('success', 'Lease updated successfully.');
    }

    public function destroy($Id)
    {
        $newlease = PropertyNewLease::findOrFail($Id);
        $newlease->delete();

        activity()
            ->causedBy(auth()->user()->Id)
            ->performedOn($newlease)
            ->event('delete')
            ->log("Deleted Lease {$newlease->Id}.");

        return redirect()->route('addlease.index')->with('success', 'Lease deleted successfully.');
    }



}
