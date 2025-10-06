<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyBlockRequest;
use App\Services\Property\PropertyRegistry\PropertyBlockService;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;
use Illuminate\Validation\Rule;


class PropertyBlockController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::PropertyStructuralView, PropertyBlock::class);
        $blocks = PropertyBlock::with('property')->get();
        return view('property.propertyregistry.structuralmapping.addblock.index',compact('blocks'));
    }
    public function create(){
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyBlock::class);
        $properties = PropertyRegistry::where('IsActive',true)->get();
        return view('property.propertyregistry.structuralmapping.addblock.create', compact('properties'));
    }

    public function store(PropertyBlockRequest $request)
    {

        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyBlock::class);
        $validated = $request->validated();

        $propertyregistry = PropertyRegistry::findOrFail($validated['PropertyID']);

        $propertyblock = PropertyBlockService::create(
            $propertyregistry,
            $validated['BlockName'],
            $validated['Description'] ?? '',
            Auth::user()
        );

           return redirect()->route('addblock.index')->with('success','property block created successfully');
    }

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyStructuralUpdate, PropertyBlock::class);
        $block = PropertyBlock::findOrFail($id);
        $properties = PropertyRegistry::all();

        return view('property.propertyregistry.structuralmapping.addblock.edit', compact('block', 'properties'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyStructuralUpdate, PropertyBlock::class);
        $validated = $request->validate([
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'BlockName' => [
                'required',
                'string',
                'max:50',
                Rule::unique(PropertyBlock::class, 'BlockName')
                    ->where(fn($query) => $query->where('PropertyID', $request->PropertyID))
                    ->ignore($id, 'Id'),
            ],
            'Description' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $block = PropertyBlock::findOrFail($id);

            $block->update([
                'PropertyID' => $validated['PropertyID'],
                'BlockName' => $validated['BlockName'],
                'Description' => $validated['Description'] ?? '',
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
        $this->authorize(PermissionEnum::PropertyStructuralDelete, PropertyBlock::class);
        try {
            $block = PropertyBlock::findOrFail($id);

            if ($block->floor()->exists()) {
                return redirect()->back()
                ->withErrors(['error' => 'This Property Block is in use and cannot be deleted.']);
            }  
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