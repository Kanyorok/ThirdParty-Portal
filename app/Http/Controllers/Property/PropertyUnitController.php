<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
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

    public function create(){
        // $properties = PropertyRegistry::all();
        // $blocks = PropertyBlock::all();
        // $floors = PropertyFloor::all();
        //$lineentries = PropertyRegistry::with('getBlockByProperty')->get();
        $lineentries = PropertyRegistry::with(['getBlockByProperty.getFloorByBlock'])->get();
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
    

    public function show($id)
    {
        $unit = PropertyUnit::find($id);
        return view('property.propertyregistry.structuralmapping.addunit.show', compact('unit'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'PropertyID' => 'required|string|max:50',
            'BlockID' => 'required|string|max:50',
            'FloorID' => 'required|string|max:50',
            'UnitCode' => 'required|string|max:50',
            'UnitSize' => 'required|integer',
            'IsRentable' => 'required|boolean',
            'CurrentStatus' => 'required|boolean',
            'Remarks' => 'required|string|max:50',
        ]);

        $unit = PropertyUnit::create([
            'PropertyID' => $request->PropertyID,
            'BlockID' => $request->BlockID,
            'FloorID' => $request->FloorID,
            'UnitCode' => $request->UnitCode,
            'UnitSize' => $request->UnitSize,
            'IsRentable' => $request->IsRentable ? 1 : 0,
            'CurrentStatus' => $request->CurrentStatus ? 1 : 0,
            'Remarks' => $request->Remarks,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        return redirect()->route('addunit.index')->with('success', 'property unit created successfully');
    }
     public function edit($id)
    {
        //Check if user has permission to edit tender categories
        //$this->authorize(PermissionEnum::PropertyUpdate, PropertyFloor::class);
        $unit = PropertyUnit::findOrFail($id);
        $floors = PropertyFloor::all();
        $blocks = PropertyBlock::all();
        $properties = PropertyRegistry::all();
        $lineentries = PropertyRegistry::with('getBlockByProperty')->get();

        return view('property.propertyregistry.structuralmapping.addunit.edit', compact('blocks', 'properties', 'lineentries', 'unit', 'floors'));
    }

    public function update(Request $request, $id)
    {
        //$this->authorize(PermissionEnum::PropertyUpdate, PropertyFloor::class);
        $validated = $request->validate([
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyBlock,Id',
            'FloorID' => 'required|exists:t_PropertyFloor,Id',
            'UnitCode' => 'required|string|max:50',
            'UnitSize' => 'required|integer',
            'IsRentable' => 'required|boolean',
            'CurrentStatus' => 'required|boolean',
            'Remarks' => 'required|string|max:50',

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
                'Remarks' => $validated['Remarks'],
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
       //$this->authorize(PermissionEnum::PropertyDelete, PropertyFloor::class);
        try {
            $unit = PropertyUnit::findOrFail($id);
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



