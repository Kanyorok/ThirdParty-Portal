<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiItem;
use App\Models\HR\KpiCategory;
use App\Models\HR\KpiUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiItemController extends Controller
{
    public function index()
    {
        $items = KpiItem::with(['category', 'unit'])->orderBy('Name')->paginate(20);
        return view('hr.config.kpi.library.index', compact('items'));
    }

    public function create()
    {
        $categories = KpiCategory::where('IsActive', 1)->orderBy('Name')->get();
        $units = KpiUnit::where('IsActive', 1)->orderBy('Name')->get();
        return view('hr.config.kpi.library.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code'          => ['required', 'string', 'max:50', 'unique:t_HRKPIItems,Code'],
            'Name'          => ['required', 'string', 'max:200'],
            'CategoryID'    => ['required', 'integer', 'exists:t_HRKPICategories,Id'],
            'UnitID'        => ['required', 'integer', 'exists:t_HRKPIUnits,Id'],
            'DefaultWeight' => ['nullable', 'numeric', 'min:0'],
            'Description'   => ['nullable', 'string', 'max:500'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        $category = KpiCategory::find($data['CategoryID']);
        $unit = KpiUnit::find($data['UnitID']);
        $data['Category'] = $category?->Name;
        $data['Unit'] = $unit?->Name;

        KpiItem::create($data);

        return redirect()->route('hr.config.kpi.library.index')->with('success', 'KPI item created.');
    }

    public function edit($id)
    {
        $item = KpiItem::findOrFail($id);
        $categories = KpiCategory::where('IsActive', 1)->orderBy('Name')->get();
        $units = KpiUnit::where('IsActive', 1)->orderBy('Name')->get();
        return view('hr.config.kpi.library.edit', compact('item', 'categories', 'units'));
    }

    public function update(Request $request, $id)
    {
        $item = KpiItem::findOrFail($id);
        $data = $request->validate([
            'Code'          => ['required', 'string', 'max:50', Rule::unique('t_HRKPIItems', 'Code')->ignore($item->Id, 'Id')],
            'Name'          => ['required', 'string', 'max:200'],
            'CategoryID'    => ['required', 'integer', 'exists:t_HRKPICategories,Id'],
            'UnitID'        => ['required', 'integer', 'exists:t_HRKPIUnits,Id'],
            'DefaultWeight' => ['nullable', 'numeric', 'min:0'],
            'Description'   => ['nullable', 'string', 'max:500'],
            'IsActive'      => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $item->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $category = KpiCategory::find($data['CategoryID']);
        $unit = KpiUnit::find($data['UnitID']);
        $data['Category'] = $category?->Name;
        $data['Unit'] = $unit?->Name;

        $item->update($data);

        return redirect()->route('hr.config.kpi.library.index')->with('success', 'KPI item updated.');
    }

    public function destroy($id)
    {
        $item = KpiItem::findOrFail($id);
        $item->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.library.index')->with('success', 'KPI item deactivated.');
    }
}
