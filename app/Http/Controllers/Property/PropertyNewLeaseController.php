<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyNewLeaseRequest;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
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
        $newleases = PropertyNewLease::all();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.index', compact('newleases'));
    }

    public function create(){
        $units = PropertyUnit::all();
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.create', compact('newtenants', 'units'));
    }

    public function show($id)
    {
        $newlease = PropertyNewLease::findOrFail($id);
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


}
