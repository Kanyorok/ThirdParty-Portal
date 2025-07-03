<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyFloorRequest;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Services\Property\PropertyRegistry\PropertyFloorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyFloorController extends Controller
{
    protected $service;
    public function __construct(PropertyFloorService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $floors = PropertyFloor::all();
        //dd($properties);
        return view('property.propertyregistry.structuralmapping.addfloor.index', compact('floors'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyFloor::class);
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
        $this->authorize(PermissionEnum::PropertyStructuralView, PropertyFloor::class);
        $floor = PropertyFloor::find($id);
        return view('property.propertyregistry.structuralmapping.addfloor.show', compact('floor'));
    }

    public function store(PropertyFloorRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyFloor::class);
        //dd($request->all());

        $validated = $request->validated();
        try {
            PropertyFloorService::create(
                PropertyRegistry::findOrFail($validated['PropertyID']),
                PropertyBlock::findOrFail($validated['BlockID']),
                $validated['FloorLabel'],
                $validated['FloorNotes'],
                auth()->user()
            );
            return redirect()->route('addfloor.index')->with('success', 'Floor added!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed: ' . $e->getMessage())->withInput();
        }

        //return redirect()->route('addfloor.index')->with('success', 'property floor created successfully');

    }


    public function edit($id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyStructuralUpdate, PropertyFloor::class);
        $floor = PropertyFloor::findOrFail($id);
        $blocks = PropertyBlock::all();
        $properties = PropertyRegistry::all();
        $lineentries = PropertyRegistry::with('getBlockByProperty')->get();

        return view('property.propertyregistry.structuralmapping.addfloor.edit', compact('blocks', 'properties', 'lineentries', 'floor'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyStructuralUpdate, PropertyFloor::class);
        $validated = $request->validate([
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockID' => 'required|exists:t_PropertyBlock,Id',
            'FloorLabel' => 'required|string|max:50',
            'FloorNotes' => 'required|string|max:100',

        ]);

        DB::beginTransaction();

        try {
            $floor = PropertyFloor::findOrFail($id);

            $floor->update([
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
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyStructuralDelete, PropertyFloor::class);
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
