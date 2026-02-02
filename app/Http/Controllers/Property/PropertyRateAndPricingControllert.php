<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRateAndPricingBulkRequest;
use App\Http\Requests\Property\PropertyRateAndPricingRequest;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRateAndPricing;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\PropertyRateAndPricingBulkService;
use App\Services\Property\PropertyRateAndPricingService;
use Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

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

            // Keep the original property, block, floor, and unit (read-only fields)
            PropertyRateAndPricingService::update(
                $pricing,
                PropertyRegistry::findOrFail($pricing->PropertyId),
                PropertyBlock::findOrFail($pricing->BlockId),
                PropertyFloor::findOrFail($pricing->FloorId),
                PropertyUnit::findOrFail($pricing->UnitId),
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

    /**
     * Show bulk upload form
     */
    public function bulkCreate()
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingCreate, PropertyRateAndPricing::class);

        return view('property.propertyrateandpricing.bulk-create');
    }

    /**
     * Process bulk upload
     */
    public function bulkStore(PropertyRateAndPricingBulkRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingCreate, PropertyRateAndPricing::class);

        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file)[0];

            // Extract headers from first row
            $headers = array_shift($data);

            // Map data to associative arrays
            $mappedData = [];
            foreach ($data as $row) {
                if (! empty(array_filter($row))) {
                    $mappedData[] = array_combine($headers, $row);
                }
            }

            // Process bulk upload
            $results = PropertyRateAndPricingBulkService::processBulkUpload($mappedData, auth()->user());

            return redirect()->route('propertyrateandpricing.index')->with([
                'success' => "Bulk upload completed. {$results['successful']} records created successfully.",
                'errors' => $results['errors'],
                'summary' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk upload: ' . $e->getMessage());

            return back()->withErrors('An error occurred during bulk upload: ' . $e->getMessage());
        }
    }

    /**
     * Download bulk upload template
     */
    public function bulkTemplate()
    {
        $this->authorize(PermissionEnum::PropertyRateAndPricingCreate, PropertyRateAndPricing::class);

        $headers = ['PropertyId', 'BlockId', 'FloorId', 'UnitId', 'Rent', 'ParkingFee', 'ServiceCharge', 'OtherCharges', 'DepositAmount', 'CurrencyId', 'TaxId'];
        $sampleData = [
            [1, 1, 1, 1, 50000, 3000, 20, 100, 34000, 75, 1],
        ];

        return Excel::download(new class ($sampleData, $headers) implements FromArray, WithHeadings {
            private $data;
            private $headers;

            public function __construct($data, $headers)
            {
                $this->data = $data;
                $this->headers = $headers;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headers;
            }
        }, 'PropertyRateAndPricing_Bulk_Template.xlsx');
    }
}
