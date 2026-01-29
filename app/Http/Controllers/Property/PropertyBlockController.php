<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyBlockBulkRequest;
use App\Http\Requests\Property\PropertyRegistry\PropertyBlockRequest;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Services\Property\PropertyRegistry\PropertyBlockBulkService;
use App\Services\Property\PropertyRegistry\PropertyBlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class PropertyBlockController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::PropertyStructuralView, PropertyBlock::class);
        $blocks = PropertyBlock::with('property')->get();

        return view('property.propertyregistry.structuralmapping.addblock.index', compact('blocks'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyBlock::class);
        $properties = PropertyRegistry::where('IsActive', true)->get();

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

        return redirect()->route('addblock.index')->with('success', 'property block created successfully');
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
                    ->where(fn ($query) => $query->where('PropertyID', $request->PropertyID))
                    ->ignore($id, 'Id'),
            ],
            'Description' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $block = PropertyBlock::findOrFail($id);

            $block->update();

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

    public function bulkCreate()
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyBlock::class);

        return view('property.propertyregistry.structuralmapping.addblock.bulk-create');
    }

    public function bulkStore(PropertyBlockBulkRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyBlock::class);

        try {
            $file = $request->file('file');

            // Parse CSV/Excel file
            $data = Excel::toArray([], $file)[0];

            // Get headers from first row
            $headers = array_shift($data);

            // Map headers to data
            $mappedData = [];
            foreach ($data as $row) {
                $mappedData[] = array_combine($headers, $row);
            }

            // Process bulk upload
            $results = PropertyBlockBulkService::processBulkUpload($mappedData, auth()->user());

            if (request()->expectsJson()) {
                return response()->json($results);
            }

            // Prepare success/error messages
            $message = "Bulk upload completed. Successful: {$results['successful']}, Failed: {$results['failed']}";

            if ($results['failed'] > 0) {
                return redirect()
                    ->route('addblock.index')
                    ->with('warning', $message)
                    ->with('errors', $results['errors']);
            }

            return redirect()
                ->route('addblock.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Bulk block upload failed: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Bulk upload failed', 'error' => $e->getMessage()], 500);
            }

            return back()
                ->withErrors(['error' => 'Failed to process bulk upload: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function bulkTemplate()
    {
        return Excel::download(
            new class () implements
                FromArray,
                WithHeadings {
                public function array(): array
                {
                    return [
                        [1, 'Block A', 'Ground floor block'],
                    ];
                }

                public function headings(): array
                {
                    return ['PropertyID', 'BlockName', 'Description'];
                }
            },
            'block_bulk_template.xlsx'
        );
    }
}
