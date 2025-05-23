<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TenderCategoryEnum;
use App\Models\Procurement\TenderCategory;

class TenderCategoryController extends Controller
{
    public function index()
    {
        $tenderCategories = TenderCategory::orderBy('Id')->get();
        return view('procurement.tendering.tendersetup.tendercategory.index', compact('tenderCategories'));
    }

    public function create()
    {
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
        $validated = $request->validate([
            'TenderCategory' => 'required|in:' . implode(',', array_column(TenderCategoryEnum::cases(), 'value')),
            'Description' => 'nullable|string',
        ]);

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
    }

    public function edit($id)
    {
        $tenderCategory = TenderCategory::findOrFail($id);
        $tenderCatOptions = TenderCategoryEnum::cases();
        
        return view('procurement.tendering.tendersetup.tendercategory.edit', [
            'tenderCategory' => $tenderCategory,
            'tenderCatOptions' => $tenderCatOptions
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'TenderCategory' => 'required|in:' . implode(',', array_column(TenderCategoryEnum::cases(), 'value')),
            'Description' => 'nullable|string',
        ]);
        $tenderCategory = TenderCategory::findOrFail($id);
        $tenderCategory->update($validated);

        return redirect()->route('tendercategory.index')
            ->with('success', 'Tender Category Updated Successfully!');
    }

    public function destroy($id)
    {
        $tenderCategory = TenderCategory::findOrFail($id);
        $tenderCategory->delete();

        return redirect()->route('tendercategory.index')
            ->with('success', 'Tender Category Deleted Successfully!');
    }
}