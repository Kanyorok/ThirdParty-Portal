<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\DisposalMethod;
use Illuminate\Http\Request;

class DisposalMethodController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = DisposalMethod::when($q, fn ($qq) =>
                    $qq->where('Code', 'like', "%$q%")
                       ->orWhere('Name', 'like', "%$q%"))
                ->orderBy('Name')->paginate(20);

        return view('assets.settings.disposalmethods.index', compact('rows', 'q'));
    }

    public function create()
    {
        return view('assets.settings.disposalmethods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_DisposalMethods,Code',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        DisposalMethod::create($data);

        return redirect()->route('assets.settings.disposal-methods.index')
            ->with('success', 'Disposal method created.');
    }

    public function edit(int $id)
    {
        $row = DisposalMethod::findOrFail($id);

        return view('assets.settings.disposalmethods.edit', compact('row'));
    }

    public function update(Request $request, int $id)
    {
        $row = DisposalMethod::findOrFail($id);

        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_DisposalMethods,Code,' . $row->Id . ',Id',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);

        return redirect()->route('assets.settings.disposal-methods.index')
            ->with('success', 'Disposal method updated.');
    }

    public function destroy(int $id)
    {
        DisposalMethod::where('Id', $id)->delete();

        return redirect()->route('assets.settings.disposal-methods.index')
            ->with('success', 'Disposal method deleted.');
    }

    public function show(int $id)
    {
        $row = \App\Models\Assets\Settings\DisposalMethod::findOrFail($id);

        return view('assets.settings.disposalmethods.show', compact('row'));
    }
}
