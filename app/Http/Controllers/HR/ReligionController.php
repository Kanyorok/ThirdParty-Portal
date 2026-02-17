<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Religion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReligionController extends Controller
{
    public function index()
    {
        $religions = Religion::orderBy('Name')->paginate(50);

        return view('hr.config.religions.index', compact('religions'));
    }

    public function create()
    {
        return view('hr.config.religions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:100', 'unique:t_HRReligions,Name'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        Religion::create($data);

        return redirect()->route('hr.config.religions.index')
            ->with('success', 'Religion created successfully.');
    }

    public function edit($id)
    {
        $religion = Religion::findOrFail($id);

        return view('hr.config.religions.edit', compact('religion'));
    }

    public function update(Request $request, $id)
    {
        $religion = Religion::findOrFail($id);

        $data = $request->validate([
            'Name' => ['required', 'string', 'max:100', Rule::unique('t_HRReligions', 'Name')->ignore($religion->Id, 'Id')],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $religion->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $religion->update($data);

        return redirect()->route('hr.config.religions.index')
            ->with('success', 'Religion updated successfully.');
    }

    public function destroy($id)
    {
        $religion = Religion::findOrFail($id);

        $religion->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.religions.index')
            ->with('success', 'Religion deactivated successfully.');
    }
}
