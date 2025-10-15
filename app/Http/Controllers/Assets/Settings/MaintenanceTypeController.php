<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\MaintenanceType;
use Illuminate\Http\Request;

class MaintenanceTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = MaintenanceType::when($q, fn($qq) =>
                    $qq->where('Code','like',"%$q%")
                       ->orWhere('Name','like',"%$q%"))
                ->orderBy('Name')->paginate(20);

        return view('assets.settings.maintenancetypes.index', compact('rows','q'));
    }

    public function create()
    {
        return view('assets.settings.maintenancetypes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_MaintenanceTypes,Code',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        MaintenanceType::create($data);

        return redirect()->route('assets.settings.maintenance-types.index')
            ->with('success','Maintenance type created.');
    }

    public function edit(int $id)
    {
        $row = MaintenanceType::findOrFail($id);
        return view('assets.settings.maintenancetypes.edit', compact('row'));
    }

    public function update(Request $request, int $id)
    {
        $row = MaintenanceType::findOrFail($id);

        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_MaintenanceTypes,Code,'.$row->Id.',Id',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);

        return redirect()->route('assets.settings.maintenance-types.index')
            ->with('success','Maintenance type updated.');
    }

    public function destroy(int $id)
    {
        MaintenanceType::where('Id',$id)->delete();

        return redirect()->route('assets.settings.maintenance-types.index')
            ->with('success','Maintenance type deleted.');
    }
    public function show(int $id)
{
    $row = \App\Models\Assets\Settings\MaintenanceType::findOrFail($id);
    return view('assets.settings.maintenancetypes.show', compact('row'));
}
}
