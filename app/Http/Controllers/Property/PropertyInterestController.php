<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyInterestRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyInterest;
use App\Models\PropertyManagement\PropertyRateAndPricing;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Services\Property\TenantAndLease\PropertyInterestService;
use Illuminate\Support\Carbon;
use Log;

class PropertyInterestController extends Controller
{
    protected PropertyInterestService $service;

    public function __construct(PropertyInterestService $service)
    {
        $this->service = $service;
    }

    /**
     * Display all property interests
     */
    public function index()
    {
        $interests = PropertyInterest::orderBy('Id', 'desc')->get();
        return view('property.tenantmanagement.leasemanagement.leaseinterest.index', compact('interests'));
    }

    public function create()
    {
        $properties = PropertyRegistry::all();
        $blocks = PropertyBlock::all();
        $floors = PropertyFloor::all();
        $units = PropertyUnit::all();
        $tenants = PropertyNewTenant::where('IsActive', true)->get();
        $frequencies = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('property.tenantmanagement.leasemanagement.leaseinterest.create', compact(
            'properties',
            'blocks',
            'floors',
            'units',
            'tenants',
            'frequencies'
        ));
    }
    public function getPricingByUnit($unitId)
    {
        $pricing = PropertyRateAndPricing::where('UnitId', $unitId)->first();

        return response()->json($pricing);
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


    public function store(PropertyInterestRequest $request)
    {
        $validated = $request->validated();

        try {
            $startDate = Carbon::parse($validated['InterestedStartDate']);
            $endDate = Carbon::parse($validated['InterestedEndDate'] ?? null);
            $paymentFrequency = CodeDetail::findOrFail($validated['PaymentFrequency']);

            $this->service->create(
                PropertyRegistry::findOrFail($validated['PropertyId']),
                PropertyBlock::findOrFail($validated['BlockId']),
                PropertyFloor::findOrFail($validated['FloorId']),
                PropertyUnit::findOrFail($validated['UnitId']),
                PropertyNewTenant::findOrFail($validated['TenantId']),
                $startDate,
                $endDate,
                $paymentFrequency,
                $validated['AdditionalInformation'],
                auth()->user()
            );

            return redirect()->route('property-interest.index')
                ->with('success', 'Property Interest created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating Property Interest: ' . $e->getMessage());
            return back()->withInput()->withErrors('An error occurred while creating the Property Interest. Please try again.');
        }
    }


    /**
     * Show single interest
     */
    public function show($id)
    {
        $interest = PropertyInterest::with('tenant', 'property', 'block', 'floor', 'unit', 'code', 'price')->findOrFail($id);
        return view('property.tenantmanagement.leasemanagement.leaseinterest.show', compact('interest'));
    }

    /**
     * Edit interest form
     */
    public function edit($id)
    {
        $interest = PropertyInterest::findOrFail($id);

        $properties = PropertyRegistry::all();
        $blocks = PropertyBlock::all();
        $floors = PropertyFloor::all();
        $units = PropertyUnit::all();
        $tenants = PropertyNewTenant::all();
        $frequencies = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('property.tenantmanagement.leasemanagement.leaseinterest.edit', compact(
            'interest',
            'properties',
            'blocks',
            'floors',
            'units',
            'tenants',
            'frequencies'
        ));
    }

    /**
     * Update interest
     */
    public function update(PropertyInterestRequest $request, $id)
    {
        $validated = $request->validated();

        try {

            $interest = PropertyInterest::findOrFail($id);

            $startDate = Carbon::parse($validated['InterestedStartDate']);
            $endDate = Carbon::parse($validated['InterestedEndDate']);
            $paymentFrequency = CodeDetail::findOrFail($validated['PaymentFrequency']);

            $this->service->update(
                $interest,
                PropertyRegistry::findOrFail($validated['PropertyId']),
                PropertyBlock::findOrFail($validated['BlockId']),
                PropertyFloor::findOrFail($validated['FloorId']),
                PropertyUnit::findOrFail($validated['UnitId']),
                PropertyNewTenant::findOrFail($validated['TenantId']),
                $startDate,
                $endDate,
                $paymentFrequency,
                $validated['AdditionalInformation'] ?? null,
                auth()->user()
            );

            return redirect()
                ->route('property-interest.index')
                ->with('success', 'Property interest updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating Property Interest: ' . $e->getMessage());
            return back()->withInput()->withErrors('An error occurred while updating. Please try again.');
        }
    }


    /**
     * Delete interest (soft delete)
     */
    public function destroy($id)
    {
        $interest = PropertyInterest::findOrFail($id);

        $interest->update([
            'DeletedBy' => auth()->user()->Id,
        ]);

        $interest->delete();

        return redirect()
            ->route('property-interest.index')
            ->with('success', 'Property interest deleted successfully.');
    }
}
