<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRateAndPricingRequest;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRateAndPricing;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\PropertyRateAndPricingService;
use Log;

class PropertyRateAndPricingControllert extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingView, PropertyRateAndPricing::class);
        $pricings = PropertyRateAndPricing::orderBy('Id', 'desc')->get();

        return view('property.propertyrateandpricing.index', compact('pricings'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingCreate, PropertyRateAndPricing::class);
        $property = PropertyRegistry::with(['getBlockByProperty.floor.units'])->where('IsActive', true)->get();
        $Taxes = FinanceTaxRuleConfiguration::all();
        $currencies = Currency::all();

        return view('property.propertyrateandpricing.create', compact('property', 'Taxes', 'currencies'));
    }

    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)->get();

        return response()->json($blocks);
    }

    public function getFloorByBlock($blockId)
    {
        $floors = PropertyFloor::where('BlockID', $blockId)->get();

        return response()->json($floors);
    }

    public function getUnitsByFloor($floorId)
    {
        $units = PropertyUnit::where('FloorID', $floorId)->get();

        return response()->json($units);
    }

    public function getPricingByUnit($unitId)
    {
        $pricing = PropertyRateAndPricing::where('UnitId', $unitId)->first();

        return response()->json($pricing);
    }

    public function store(PropertyRateAndPricingRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingCreate, PropertyRateAndPricing::class);
        $validated = $request->validated();

        try {
            PropertyRateAndPricingService::create(
                PropertyRegistry::findOrFail($validated['PropertyId']),
                PropertyBlock::findOrFail($validated['BlockId']),
                PropertyFloor::findOrFail($validated['FloorId']),
                PropertyUnit::findOrFail($validated['UnitId']),
                $validated['Rent'],
                $validated['ParkingFee'],
                $validated['ServiceCharge'],
                $validated['OtherCharges'],
                $validated['DepositAmount'],
                Currency::findOrFail($validated['CurrencyId']),
                FinanceTaxRuleConfiguration::findOrFail($validated['TaxId']),
                auth()->user()
            );

            return redirect()->route('propertyrateandpricing.index')
                ->with('success', 'Property Rate and Pricing created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating Property Rate and Pricing: ' . $e->getMessage());

            return back()->withErrors('An error occurred while creating the Property Rate and Pricing. Please try again.');
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingView, PropertyRateAndPricing::class);
        $pricing = PropertyRateAndPricing::findOrFail($id);

        return view('property.propertyrateandpricing.show', compact('pricing'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingUpdate, PropertyRateAndPricing::class);
        $pricing = PropertyRateAndPricing::findOrFail($id);
        $property = PropertyRegistry::with(['getBlockByProperty.floor.units'])->where('IsActive', true)->get();
        $Taxes = FinanceTaxRuleConfiguration::all();
        $currencies = Currency::all();

        return view('property.propertyrateandpricing.edit', compact('pricing', 'property', 'Taxes', 'currencies'));
    }

    public function update(PropertyRateAndPricingRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingUpdate, PropertyRateAndPricing::class);
        $validated = $request->validated();

        try {
            $pricing = PropertyRateAndPricing::findOrFail($id);

            PropertyRateAndPricingService::update(
                $pricing,
                PropertyRegistry::findOrFail($validated['PropertyId']),
                PropertyBlock::findOrFail($validated['BlockId']),
                PropertyFloor::findOrFail($validated['FloorId']),
                PropertyUnit::findOrFail($validated['UnitId']),
                $validated['Rent'],
                $validated['ParkingFee'],
                $validated['ServiceCharge'],
                $validated['OtherCharges'],
                $validated['DepositAmount'],
                Currency::findOrFail($validated['CurrencyId']),
                FinanceTaxRuleConfiguration::findOrFail($validated['TaxId']),
                auth()->user()
            );

            return redirect()->route('propertyrateandpricing.index')
                ->with('success', 'Property Rate and Pricing updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating Property Rate and Pricing: ' . $e->getMessage());

            return back()->withErrors('An error occurred while updating the Property Rate and Pricing. Please try again.');
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingDelete, PropertyRateAndPricing::class);

        try {
            $pricing = PropertyRateAndPricing::findOrFail($id);
            $pricing->delete();

            return redirect()->route('propertyrateandpricing.index')
                ->with('success', 'Property Rate and Pricing deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting Property Rate and Pricing: ' . $e->getMessage());

            return back()->withErrors('An error occurred while deleting the Property Rate and Pricing. Please try again.');
        }
    }
}
