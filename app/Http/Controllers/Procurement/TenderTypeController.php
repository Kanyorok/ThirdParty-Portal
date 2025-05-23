<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderType;
use Illuminate\Http\Request;
use App\Enums\TenderTypeEnum;

class TenderTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $tenderTypes = TenderType::orderBy('Id')->get();
        return view('procurement.tendering.tendersetup.tendertype.index', compact('tenderTypes'));
    }

    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $defaultType = TenderTypeEnum::cases()[0]->value;
        $newTypeCode = TenderType::generateTypeCode($defaultType);
        
        return view('procurement.tendering.tendersetup.tendertype.create', [
            'newTypeCode' => $newTypeCode,
            'tenderTypeOptions' => TenderTypeEnum::cases(),
            'defaultType' => $defaultType
        ]);
    }

    public function store(Request $request)
    {
         $validated = $request->validate([
            'TenderType' => 'required',
            'Description' => 'nullable|string',
            'TypeCode' => 'required|string|max:20|unique:t_TenderTypes,TypeCode',
        ]);

        do {
            $newTypeCode = TenderType::generateTypeCode($validated['TenderType']);
        } while (TenderType::where('TypeCode', $newTypeCode)->exists());

        TenderType::create([
            'TypeCode' => $newTypeCode,
            'TenderType' => $validated['TenderType'],
            'Description' => $validated['Description'],
            'CreatedBy' => auth()->user()->Id,
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->user()->Id,
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('tendertype.index')
            ->with('success', 'Tender Type Created Successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tenderType = TenderType::findOrFail($id);
        $tenderTypeOptions = TenderTypeEnum::cases();
        return view('procurement.tendering.tendersetup.tendertype.edit', compact('tenderType', 'tenderTypeOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'TypeCode' => 'required|string|max:20|unique:t_TenderTypes,TypeCode,' . $id . ',Id',
            'TenderType' => 'required|in:' . implode(',', array_column(TenderTypeEnum::cases(), 'value')),
            'Description' => 'nullable|string',
        ]);

        $tenderType = TenderType::findOrFail($id);
        $tenderType->update($validated);

        return redirect()->route('tendertype.index')
            ->with('success', 'Tender type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tenderType = TenderType::findOrFail($id);
        $tenderType->delete();

        return redirect()->route('tendertype.index')
            ->with('success', 'Tender type deleted successfully.');
    }
}