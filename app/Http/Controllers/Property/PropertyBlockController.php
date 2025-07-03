<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyBlockRequest;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Services\Property\PropertyRegistry\PropertyBlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PropertyBlockController extends Controller
{
    //
    public function index()
    {
        $blocks = PropertyBlock::with('property')->get();
        return view('property.propertyregistry.structuralmapping.addblock.index',compact('blocks'));
    }
    public function create(){
        $properties = PropertyRegistry::all();
        return view('property.propertyregistry.structuralmapping.addblock.create', compact('properties'));
    }

    public function store(PropertyBlockRequest $request)
    {

        $validated = $request->validated();

        $propertyregistry = PropertyRegistry::findOrFail($validated['PropertyID']);

        $propertyblock = PropertyBlockService::create(
            $propertyregistry,
            $validated['BlockName'] ?? '--',
            $validated['Description'] ?? '--',
            auth()->user()
        );

           return redirect()->route('addblock.index')->with('success','property block created successfully');
    }

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
        //$this->authorize(PermissionEnum::PropertyCategoryUpdate, CategoryMaster::class);
        $block = PropertyBlock::findOrFail($id);
        $properties = PropertyRegistry::all();

        return view('property.propertyregistry.structuralmapping.addblock.edit', compact('block', 'properties'));
    }

    public function update(Request $request, $id)
    {
        //$this->authorize(PermissionEnum::PropertyCategoryUpdate, CategoryMaster::class);
        $validated = $request->validate([
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockName' => 'required|string|max:50',
            'Description' => 'required|string|max:100',

        ]);

        DB::beginTransaction();

        try {
            $block = PropertyBlock::findOrFail($id);

            $block->update([
                'PropertyID' => $validated['PropertyID'],
                'BlockName' => $validated['BlockName'],
                'Description' => $validated['Description'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($block)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Block');

            return redirect()->route('addblock.index')->with('success', 'Block updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update property block:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update property block'])->withInput();
        }
    }

    public function destroy($id)
    {
        //Check if user has permission to delete property categories
        //$this->authorize(PermissionEnum::PropertyCategoryDelete, CategoryMaster::class);
        try {
            $block = PropertyBlock::findOrFail($id);
            $block->delete();

            return redirect()->route('addblock.index')
                ->with('success', 'Property Block Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property block: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Block. Please try again.'])
                ->withInput();
        }
    }

}
