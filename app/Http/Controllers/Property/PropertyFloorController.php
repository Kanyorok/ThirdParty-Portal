<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;    
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyBlock;

class PropertyFloorController extends Controller
{
    //
    public function index()
    {
        $floors = PropertyFloor::all();
        //dd($properties);
        return view('property.propertyregistry.structuralmapping.addfloor.index', compact('floors'));
    }
    public function create(){
         $lineentries = PropertyRegistry::with('getBlockByProperty')->get();

        return view('property.propertyregistry.structuralmapping.addfloor.create', compact('lineentries'));
    }
    public function getBlockByProperty($propertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $propertyId)->get();
        return response()->json($blocks);
    }

    public function show($id)
    {
        $floor = PropertyFloor::find($id);
        return view('property.propertyregistry.structuralmapping.addfloor.show', compact('floor'));
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $request->validate([
            'PropertyID' => 'required|string|max:50',
            'BlockID' => 'required|string|max:50',
            'FloorLabel' => 'required|string|max:50',
            'FloorNotes' => 'required|string|max:100',
        ]);

        $floor = PropertyFloor::create([
            'PropertyID' => $request->PropertyID,
            'BlockID' => $request->BlockID,
            'FloorLabel' => $request->FloorLabel,
            'FloorNotes' => $request->FloorNotes,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('addfloor.index')->with('success', 'property floor created successfully');

    }
       public function edit($id)
    {
        //Check if user has permission to edit tender categories
        //$this->authorize(PermissionEnum::PropertyCategoryUpdate, CategoryMaster::class);
        $floor = PropertyFloor::findOrFail($id); 
        $blocks = PropertyBlock::all();
        $properties = PropertyRegistry::all();
        

        return view('property.propertyregistry.structuralmapping.addfloor.edit', compact('blocks', 'properties', 'floor'));
    }

    public function update(Request $request, $id)
    {
        //$this->authorize(PermissionEnum::PropertyCategoryUpdate, CategoryMaster::class);
        $validated = $request->validate([
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyBlock,Id',
            'FloorLabel' => 'required|string|max:50',
            'FloorNotes' => 'required|string|max:100',

        ]);

        DB::beginTransaction();

        try {
            $block = PropertyBlock::findOrFail($id);

            $block->update([
                'PropertyID' => $validated['PropertyID'],
                'BlockID' => $validated['BlockID'],
                'FloorLabel' => $validated['FloorLabel'],
                'FloorNotes' => $validated['FloorNotes'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($floor)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Floor');

            return redirect()->route('addfloor.index')->with('success', 'Floor updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update property floor:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update property floor'])->withInput();
        }
    }

    public function destroy($id)
    {
        //Check if user has permission to delete property categories
       //$this->authorize(PermissionEnum::PropertyCategoryDelete, CategoryMaster::class);
        try {
            $floor = PropertyFloor::findOrFail($id);
            $floor->delete();

            return redirect()->route('addfloor.index')
                ->with('success', 'Property Floor Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property floor: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Floor. Please try again.'])
                ->withInput();
        }
    }
}
