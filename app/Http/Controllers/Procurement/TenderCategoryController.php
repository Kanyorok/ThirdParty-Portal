<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TenderCategoryEnum;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TenderCategoryController extends Controller
{
    public function index()
    {
        //Check if user has permission to view tender categories
        $this->authorize(PermissionEnum::TenderRead, Tender::class);
        $tenderCategories = TenderCategory::orderBy('Id')->get();
        return view('procurement.tendering.tendersetup.tendercategory.index', compact('tenderCategories'));
    }

    public function create()
    {
        //Check if user has permission to create tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        $tenderCatOptions = TenderCategoryEnum::cases();

        return view('procurement.tendering.tendersetup.tendercategory.create', [
            'tenderCatOptions' => $tenderCatOptions,
        ]);
    }

    /**
     * AJAX endpoint to generate category code dynamically
     */
    public function generateCategoryCode(Request $request)
    {
        $categoryType = $request->input('category_type');

        if (!$categoryType) {
            return response()->json([
                'ok' => false,
                'code' => null,
                'message' => 'Category type is required'
            ], 400);
        }

        try {
            // Generate unique code
            $attempts = 0;
            do {
                $newCatCode = TenderCategory::generateCatCode($categoryType);
                $attempts++;

                // Prevent infinite loop
                if ($attempts > 100) {
                    throw new \Exception('Could not generate unique category code after 100 attempts');
                }
            } while (TenderCategory::where('CategoryCode', $newCatCode)->exists());

            Log::info('Category code generated', [
                'category_type' => $categoryType,
                'code' => $newCatCode,
                'attempts' => $attempts
            ]);

            return response()->json([
                'ok' => true,
                'code' => $newCatCode
            ]);
        } catch (\Throwable $th) {
            Log::error('Error generating category code: ' . $th->getMessage(), [
                'category_type' => $categoryType,
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'ok' => false,
                'code' => null,
                'message' => 'Failed to generate category code'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        //Check if user has permission to create tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        $validated = $request->validate([
            'TenderCategory' => 'required|string|max:255',
            'CategoryCode' => 'required|string|max:50|unique:t_TenderCategories,CategoryCode',
            'Description' => 'nullable|string',
        ]);

        try {

            $enum = TenderCategoryEnum::from($validated['TenderCategory']);
            // Create the category
            $category = TenderCategory::create([
                'CategoryCode' => $validated['CategoryCode'],
                'TenderCategory' => $enum->displayName(),
                'Description' => $validated['Description'] ?? null,
                'CreatedBy' => auth()->user()->Id,
                'ModifiedBy' => auth()->user()->Id,
            ]);

            Log::info('Tender category created', [
                'category_id' => $category->Id,
                'category_code' => $category->CategoryCode,
                'category_type' => $category->TenderCategory,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('tendercategory.index')
                ->with('success', 'Tender Category Created Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error creating tender category: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create Tender Category. Please try again.'])
                ->withInput();
        }
    }

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        $tenderCategory = TenderCategory::findOrFail($id);
        $tenderCatOptions = TenderCategoryEnum::cases();

        return view('procurement.tendering.tendersetup.tendercategory.edit', [
            'tenderCategory' => $tenderCategory,
            'tenderCatOptions' => $tenderCatOptions
        ]);
    }

    public function update(Request $request, $id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        $validated = $request->validate([
            'TenderCategory' => 'required|string|max:255',
            'Description' => 'nullable|string',
        ]);

        try {
            $tenderCategory = TenderCategory::findOrFail($id);
            $tenderCategory->update([
                'TenderCategory' => $validated['TenderCategory'],
                'Description' => $validated['Description'] ?? null,
                'ModifiedBy' => auth()->user()->Id,
            ]);

            Log::info('Tender category updated', [
                'category_id' => $id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('tendercategory.index')
                ->with('success', 'Tender Category Updated Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error updating tender category: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update Tender Category. Please try again.'])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        //Check if user has permission to delete tender categories
        $this->authorize(PermissionEnum::TenderDelete, Tender::class);
        try {
            $tenderCategory = TenderCategory::findOrFail($id);
            $tenderCategory->delete();

            return redirect()->route('tendercategory.index')
                ->with('success', 'Tender Category Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting tender category: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Tender Category. Please try again.']);
        }
    }

   public function itemTypes($id)
{
    //Check if user has permission to edit tender categories
    $this->authorize(PermissionEnum::TenderWrite, Tender::class);

    $category = TenderCategory::findOrFail($id);

    // Use the ItemType model with its relationship - eager load the type relationship
    $allTypes = \App\Models\Inventory\ItemType::with('type')
        ->where('Active', 1)
        ->get()
        ->map(function($item) {
            return (object)[
                'Id' => $item->Id,
                'TypeName' => $item->type ? $item->type->Description : "Type {$item->TypeName}",
                'StockTracked' => $item->StockTracked,
                'RequiresTagging' => $item->RequiresTagging,
                'Active' => $item->Active,
            ];
        })
        ->sortBy('TypeName')
        ->values();

    // Debug logging
    Log::info('ItemTypes Query Results', [
        'count' => $allTypes->count(),
        'sample' => $allTypes->take(3)->toArray()
    ]);

    // Ensure selected IDs are integers
    $selected = DB::table('t_TenderCategoryItemTypes')
        ->where('TenderCategoryId', $id)
        ->pluck('ItemTypeId')
        ->map(fn($id) => (int)$id)
        ->toArray();

    return view('procurement.tendering.tendersetup.tendercategory.map_itemtypes', compact('category', 'allTypes', 'selected'));
}

    // Update mapping: replace rows with submitted set
    public function updateItemTypes(Request $request, $id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        $validated = $request->validate([
            'item_type_ids' => 'nullable|array',
            'item_type_ids.*' => 'integer|exists:t_ItemTypes,Id',
        ]);

        $ids = collect($validated['item_type_ids'] ?? [])
            ->map(fn($v) => (int)$v)
            ->filter()
            ->unique()
            ->values();

        try {
            DB::transaction(function () use ($id, $ids) {
                // Delete existing mappings
                DB::table('t_TenderCategoryItemTypes')
                    ->where('TenderCategoryId', $id)
                    ->delete();

                // Insert new mappings (only required fields)
                foreach ($ids as $typeId) {
                    DB::table('t_TenderCategoryItemTypes')->insert([
                        'TenderCategoryId' => $id,
                        'ItemTypeId' => $typeId,
                        'IsActive' => 1,
                    ]);
                }
            });

            Log::info('Tender category item types updated', [
                'tender_category_id' => $id,
                'item_type_ids' => $ids->toArray(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('tendercategory.itemtypes', $id)
                ->with('success', 'Item type mappings updated successfully.');

        } catch (\Throwable $th) {
            Log::error('Error updating tender category item types: ' . $th->getMessage(), [
                'tender_category_id' => $id,
                'trace' => $th->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update item type mappings. Please try again.'])
                ->withInput();
        }
    }
}
