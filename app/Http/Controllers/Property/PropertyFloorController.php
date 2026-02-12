<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyRegistry\PropertyFloorBulkRequest;
use App\Http\Requests\Property\PropertyRegistry\PropertyFloorRequest;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Services\Property\PropertyRegistry\PropertyFloorBulkService;
use App\Services\Property\PropertyRegistry\PropertyFloorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class PropertyFloorController extends Controller
{
    protected $service;

    public function __construct(PropertyFloorService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize(PermissionEnum::PropertyStructuralView, PropertyFloor::class);
        $floors = PropertyFloor::all();

        return view('property.propertyregistry.structuralmapping.addfloor.index', compact('floors'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyFloor::class);
        $lineentries = PropertyRegistry::with('getBlockByProperty')->where('IsActive', true)->get();


        return view('property.propertyregistry.structuralmapping.addfloor.create', compact('lineentries'));
    }

    public function getBlocksForFloor($PropertyId)
    {
        $blocks = PropertyBlock::where('PropertyID', $PropertyId)->get();

        return response()->json($blocks);
    }

    public function store(PropertyFloorRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyFloor::class);

        $validated = $request->validated();

        try {
            PropertyFloorService::create(
                PropertyRegistry::findOrFail($validated['PropertyID']),
                PropertyBlock::findOrFail($validated['BlockID']),
                $validated['FloorLabel'],
                $validated['FloorNotes'] ?? '',
                Auth::user()
            );

            return redirect()->route('addfloor.index')->with('success', 'Floor added!');
        } catch (Exception $e) {
            return back()->withErrors('Failed: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
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
            'FloorLabel' => [
                'required',
                'string',
                'max:50',
                Rule::unique(PropertyFloor::class, 'FloorLabel')
                    ->where(
                        fn ($query) => $query
                        ->where('PropertyID', $request->PropertyID)
                        ->where('BlockID', $request->BlockID)
                    )
                    ->ignore($id, 'Id'), // Exclude current record
            ],
            'FloorNotes' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $floor = PropertyFloor::findOrFail($id);

            $floor->update($validated);

            DB::commit();
            activity()
                ->performedOn($floor)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Floor');

            return redirect()->route('addfloor.index')->with('success', 'Floor updated successfully');
        } catch (Throwable $th) {
            DB::rollBack();

            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyStructuralDelete, PropertyFloor::class);

        try {
            $floor = PropertyFloor::findOrFail($id);

            if ($floor->units()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Property Floor is in use and cannot be deleted.']);
            }

            $floor->delete($floor->id);

            return redirect()->route('addfloor.index')
                ->with('success', 'Property Floor Deleted Successfully!');
        } catch (Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property floor: ' . $th->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Floor. Please try again.'])
                ->withInput();
        }
    }

    public function bulkCreate()
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyFloor::class);

        return view('property.propertyregistry.structuralmapping.addfloor.bulk-create');
    }

    public function bulkStore(PropertyFloorBulkRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyStructuralCreate, PropertyFloor::class);

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
            $results = PropertyFloorBulkService::processBulkUpload($mappedData, auth()->user());

            if (request()->expectsJson()) {
                return response()->json($results);
            }

            // Prepare success/error messages
            $message = "Bulk upload completed. Successful: {$results['successful']}, Failed: {$results['failed']}";

            if ($results['failed'] > 0) {
                return redirect()
                    ->route('addfloor.index')
                    ->with('warning', $message)
                    ->with('errors', $results['errors']);
            }

            return redirect()
                ->route('addfloor.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Bulk floor upload failed: ' . $e->getMessage());

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
                        ['1', '1', 'Floor 1', 'Ground floor'],
                    ];
                }

                public function headings(): array
                {
                    return ['PropertyID', 'BlockID', 'FloorLabel', 'FloorNotes'];
                }
            },
            'floor_bulk_template.xlsx'
        );
    }
}
