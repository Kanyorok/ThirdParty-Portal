<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\PolicyCategory;
use Illuminate\Http\Request;

class PolicyCategoryController extends Controller
{
    public function index()
    {
        $categories = PolicyCategory::orderBy('Name')->get();
        return view('legal.setup.policy_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('legal.setup.policy_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
        ]);

        PolicyCategory::create($validated + ['IsActive' => 1]);
        return redirect()->route('legal.setup.policy_categories.index')
            ->with('success', 'Policy Category added successfully.');
    }

    public function edit($id)
    {
        $category = PolicyCategory::findOrFail($id);
        return view('legal.setup.policy_categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = PolicyCategory::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        $category->update($validated);
        return redirect()->route('legal.setup.policy_categories.index')
            ->with('success', 'Policy Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = PolicyCategory::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Policy Category deleted.');
    }
}
