<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiCategoryController extends Controller
{
    public function index()
    {
        $categories = KpiCategory::orderBy('Name')->paginate(20);
        return view('hr.config.kpi.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('hr.config.kpi.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRKPICategories,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiCategory::create($data);

        return redirect()->route('hr.config.kpi.categories.index')->with('success', 'Category created.');
    }

    public function edit($id)
    {
        $category = KpiCategory::findOrFail($id);
        return view('hr.config.kpi.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = KpiCategory::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRKPICategories', 'Code')->ignore($category->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $category->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $category->update($data);

        return redirect()->route('hr.config.kpi.categories.index')->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        $category = KpiCategory::findOrFail($id);
        $category->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.categories.index')->with('success', 'Category deactivated.');
    }
}
