<?php
namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\AssetLocation;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    public function index(Request $request) {
        $q = $request->get('q');
        $rows = AssetLocation::when($q, fn($qq) =>
                    $qq->where('Code','like',"%$q%")
                       ->orWhere('Site','like',"%$q%")
                       ->orWhere('Building','like',"%$q%"))
                ->orderBy('Site')->paginate(20);
        return view('assets.settings.locations.index', compact('rows','q'));
    }

    public function create() {
        $parents = AssetLocation::orderBy('Site')->get(['Id','Code','Site','Building','Floor','Room']);
        return view('assets.settings.locations.create', compact('parents'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_AssetLocations,Code',
            'Site' => 'required|max:100',
            'Building' => 'nullable|max:100',
            'Floor' => 'nullable|max:50',
            'Room' => 'nullable|max:50',
            'ParentID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive');
        AssetLocation::create($data);
        return redirect()->route('assets.settings.locations.index')->with('success','Location created.');
    }

    public function edit(int $id) {
        $row = AssetLocation::findOrFail($id);
        $parents = AssetLocation::where('Id','<>',$id)->orderBy('Site')->get(['Id','Code','Site','Building','Floor','Room']);
        return view('assets.settings.locations.edit', compact('row','parents'));
    }

    public function update(Request $request, int $id) {
        $row = AssetLocation::findOrFail($id);
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_AssetLocations,Code,'.$row->Id.',Id',
            'Site' => 'required|max:100',
            'Building' => 'nullable|max:100',
            'Floor' => 'nullable|max:50',
            'Room' => 'nullable|max:50',
            'ParentID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);
        return redirect()->route('assets.settings.locations.index')->with('success','Location updated.');
    }

    public function destroy(int $id) {
        AssetLocation::where('Id',$id)->delete();
        return redirect()->route('assets.settings.locations.index')->with('success','Location deleted.');
    }
}
