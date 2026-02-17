<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryOffenceCategory;
use Illuminate\Http\Request;

class DisciplinaryOffenceCategoryController extends Controller
{
    public function index()
    {
        $categories = DisciplinaryOffenceCategory::orderBy('Name')->get();
        return view('hr.discipline.offence_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('hr.discipline.offence_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        DisciplinaryOffenceCategory::create([
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.offence-categories.index')->with('success', 'Category saved.');
    }

    public function edit($id)
    {
        $category = DisciplinaryOffenceCategory::findOrFail($id);
        return view('hr.discipline.offence_categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = DisciplinaryOffenceCategory::findOrFail($id);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $category->update([
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.offence-categories.index')->with('success', 'Category updated.');
    }
}
