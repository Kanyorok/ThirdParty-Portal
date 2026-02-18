<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchConfigController extends Controller
{
    public function index()
    {
        $branches = Branch::orderBy('Name')->paginate(20);

        return view('hr.config.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('hr.config.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'BranchID' => ['required', 'string', 'max:50', 'unique:t_Branches,BranchID'],
            'Name' => ['required', 'string', 'max:150'],
            'Address' => ['nullable', 'string', 'max:255'],
            'City' => ['nullable', 'string', 'max:100'],
            'Country' => ['nullable', 'string', 'max:100'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Email' => ['nullable', 'string', 'max:150'],
        ]);

        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        $data['IsActive'] = 1;

        Branch::create($data);

        return redirect()->route('hr.config.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);

        return view('hr.config.branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $data = $request->validate([
            'BranchID' => ['required', 'string', 'max:50', Rule::unique('t_Branches', 'BranchID')->ignore($branch->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Address' => ['nullable', 'string', 'max:255'],
            'City' => ['nullable', 'string', 'max:100'],
            'Country' => ['nullable', 'string', 'max:100'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Email' => ['nullable', 'string', 'max:150'],
        ]);

        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $branch->update($data);

        return redirect()->route('hr.config.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->update([
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
            'IsActive' => 0,
        ]);

        return redirect()->route('hr.config.branches.index')
            ->with('success', 'Branch deactivated successfully.');
    }
}
