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
        $defaultCategory = TenderCategoryEnum::cases()[0]->value;
        $newCatCode = TenderCategory::generateCatCode($defaultCategory);
        $tenderCatOptions = TenderCategoryEnum::cases();

        return view('procurement.tendering.tendersetup.tendercategory.create', [
            'newCatCode' => $newCatCode,
            'tenderCatOptions' => $tenderCatOptions,
            'defaultCategory' => $defaultCategory
        ]);
    }

    public function store(Request $request)
    {
        //Check if user has permission to create tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        $validated = $request->validate([
            'TenderCategory' => 'required',
            'Description' => 'nullable|string',
        ]);

        try {
            // Generate unique category code
            do {
                $newCatCode = TenderCategory::generateCatCode($validated['TenderCategory']);
            } while (TenderCategory::where('CategoryCode', $newCatCode)->exists());

            // Create the category
            TenderCategory::create([
                'CategoryCode' => $newCatCode,
                'TenderCategory' => $validated['TenderCategory'],
                'Description' => $validated['Description'],
                'CreatedBy' => auth()->user()->Id,
                'ModifiedBy' => auth()->user()->Id,
            ]);

            return redirect()->route('tendercategory.index')
                ->with('success', 'Tender Category Created Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error creating tender category: ' . $th->getMessage());
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
            'TenderCategory' => 'required',
            'Description' => 'nullable|string',
        ]);
        try {
            $tenderCategory = TenderCategory::findOrFail($id);
            $tenderCategory->update($validated);

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
                ->withErrors(['error' => 'Failed to delete Tender Category. Please try again.'])
                ->withInput();
        }
    }

    // Show mapping screen: TenderCategory ↔ ItemTypes
    public function itemTypes($id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        $category = TenderCategory::findOrFail($id);
        $allTypes = DB::table('t_ItemTypes')
            ->join('t_CodeDetails', 't_ItemTypes.TypeName', '=', 't_CodeDetails.Id')
            ->select('t_ItemTypes.Id', 't_CodeDetails.Description as TypeName', 't_ItemTypes.StockTracked', 't_ItemTypes.RequiresTagging', 't_ItemTypes.Active')
            ->orderBy('t_CodeDetails.Description')
            ->get();
        $selected = DB::table('t_TenderCategoryItemTypes')->where('TenderCategoryId', $id)->pluck('ItemTypeId')->toArray();

        return view('procurement.tendering.tendersetup.tendercategory.map_itemtypes', compact('category', 'allTypes', 'selected'));
    }

    // Update mapping: replace rows with submitted set
    public function updateItemTypes(\Illuminate\Http\Request $request, $id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        $ids = collect($request->input('item_type_ids', []))->map(fn($v) => (int)$v)->filter()->unique()->values();

        DB::transaction(function () use ($id, $ids) {
            DB::table('t_TenderCategoryItemTypes')->where('TenderCategoryId', $id)->delete();
            foreach ($ids as $typeId) {
                DB::table('t_TenderCategoryItemTypes')->insert([
                    'TenderCategoryId' => $id,
                    'ItemTypeId' => $typeId,
                    'IsActive' => 1,
                ]);
            }
        });

        return redirect()->route('tendercategory.itemtypes', $id)->with('success', 'Mapping updated.');
    }
}
