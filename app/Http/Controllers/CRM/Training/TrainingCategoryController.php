<?php

namespace App\Http\Controllers\CRM\Training;

use App\Http\Controllers\Controller;
use App\Models\CRM\Training\TrainingCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingCategoryController extends Controller
{
    public function index()
    {
        $categories = TrainingCategory::orderBy('Name')->paginate(30);

        return view('crm.training.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('crm.training.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['nullable', 'string', 'max:30', 'unique:t_CRMTrainingCategories,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        TrainingCategory::create($data);

        return redirect()->route('crm.training.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit($id)
    {
        $category = TrainingCategory::findOrFail($id);

        return view('crm.training.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = TrainingCategory::findOrFail($id);

        $data = $request->validate([
            'Code' => ['nullable', 'string', 'max:30', Rule::unique('t_CRMTrainingCategories', 'Code')->ignore($category->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $category->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $category->update($data);

        return redirect()->route('crm.training.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        $category = TrainingCategory::findOrFail($id);
        $category->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('crm.training.categories.index')
            ->with('success', 'Category deactivated.');
    }
}
