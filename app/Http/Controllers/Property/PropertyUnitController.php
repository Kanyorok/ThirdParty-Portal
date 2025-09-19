<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyUnitRequest;
use App\Services\Property\PropertyRegistry\PropertyUnitService;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;


class PropertyUnitController extends Controller
{
    //
    public function index()
    {
        $units = PropertyUnit::all();
        return view('property.propertyregistry.structuralmapping.addunit.index', compact('units'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyUnit::class);
        $lineentries = PropertyRegistry::with(['getBlockByProperty.floor'])->where('IsActive',true)->get();
        return view('property.propertyregistry.structuralmapping.addunit.create', compact('lineentries'));
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


    public function store(PropertyUnitRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyUnit::class);
        $validated = $request->validated();

        try {
            PropertyUnitService::create(
                PropertyRegistry::findOrFail($validated['PropertyID']),
                PropertyBlock::findOrFail($validated['BlockID']),
                PropertyFloor::findOrFail($validated['FloorID']),
                $validated['UnitCode'],
                $validated['UnitSize'],
                $validated['IsRentable'] ? 1 : 0,
                $validated['CurrentStatus'] ? 1 : 0,
                $validated['Remarks'] ?? '',
                auth()->user()

            );
            return redirect()->route('addunit.index')->with('success', 'property unit Added successfully');
        } catch (\Exception $e) {

            return back()->withErrors('Failed:' . $e->getMessage())->withInput();

        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyStructuralUpdate, PropertyUnit::class);
        
        $unit = PropertyUnit::findOrFail($id);
        $floors = PropertyFloor::all();
        $blocks = PropertyBlock::all();
        $properties = PropertyRegistry::all();
        $lineentries = PropertyRegistry::with('getBlockByProperty.floor')->get();

        return view('property.propertyregistry.structuralmapping.addunit.edit', compact('blocks', 'properties', 'lineentries', 'unit', 'floors'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyStructuralUpdate, PropertyUnit::class);
        $validated = $request->validate([
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyBlock,Id',
            'FloorID' => 'required|exists:t_PropertyFloor,Id',
            'UnitCode' => 'required|string|max:50',
            'UnitSize' => 'required|integer',
            'IsRentable' => 'required|boolean',
            'CurrentStatus' => 'required|boolean',
            'Remarks' => 'nullable|string|max:50',

        ]);

        DB::beginTransaction();

        try {
            $unit = PropertyUnit::findOrFail($id);

            $unit->update([
                'PropertyID' => $validated['PropertyID'],
                'BlockID' => $validated['BlockID'],
                'FloorID' => $validated['FloorID'],
                'UnitCode' => $validated['UnitCode'],
                'UnitSize' => $validated['UnitSize'],
                'IsRentable' => $validated['IsRentable'],
                'CurrentStatus' => $validated['CurrentStatus'],
                'Remarks' => $validated['Remarks'] ?? '',
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($unit)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Unit');

            return redirect()->route('addunit.index')->with('success', 'Unit updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        //Check if user has permission to delete property categories
        $this->authorize(PermissionEnum::PropertyStructuralDelete, PropertyUnit::class);
        try {
            $unit = PropertyUnit::findOrFail($id);

            if ($unit->unitlease()->exists()) {
                return redirect()->back()
                ->withErrors(['error' => 'This Property unit is in use and cannot be deleted.']);
            }
            
            $unit->delete();

            return redirect()->route('addunit.index')
                ->with('success', 'Property Unit Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property unit: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Unit. Please try again.'])
                ->withInput();
        }
    }
}



