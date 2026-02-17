<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\SharedDocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SharedDocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = SharedDocumentCategory::orderBy('Name')->paginate(30);
        return view('hr.shared-docs.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('hr.shared-docs.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['nullable', 'string', 'max:30', 'unique:t_HRSharedDocumentCategories,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        SharedDocumentCategory::create($data);

        return redirect()->route('hr.shared-docs.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit($id)
    {
        $category = SharedDocumentCategory::findOrFail($id);
        return view('hr.shared-docs.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = SharedDocumentCategory::findOrFail($id);

        $data = $request->validate([
            'Code' => ['nullable', 'string', 'max:30', Rule::unique('t_HRSharedDocumentCategories', 'Code')->ignore($category->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $category->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $category->update($data);

        return redirect()->route('hr.shared-docs.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        $category = SharedDocumentCategory::findOrFail($id);
        $category->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.shared-docs.categories.index')
            ->with('success', 'Category deactivated.');
    }
}
