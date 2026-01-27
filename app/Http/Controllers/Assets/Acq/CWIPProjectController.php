<?php

namespace App\Http\Controllers\Assets\Acq;

use App\Http\Controllers\Controller;
use App\Models\Assets\Acq\{CWIPLine, CWIPProject};
use App\Models\Assets\Settings\{AssetLocation, FixedAssetClass};
use Illuminate\Http\Request;

class CWIPProjectController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = CWIPProject::when($q, fn ($qq) => $qq->where('ProjectCode', 'like', "%$q%")
                                                 ->orWhere('ProjectName', 'like', "%$q%"))
                ->orderBy('ProjectCode')->paginate(20);

        return view('assets.acq.cwip.projects.index', compact('rows', 'q'));
    }

    public function create()
    {
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = AssetLocation::orderBy('Site')->get();

        return view('assets.acq.cwip.projects.create', compact('classes', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ProjectCode' => 'required|max:50|unique:t_CWIPProjects,ProjectCode',
            'ProjectName' => 'required|max:200',
            'StartDate' => 'nullable|date',
            'EndDate' => 'nullable|date|after_or_equal:StartDate',
            'Status' => 'required|in:Open,Closed',
            'ClassID' => 'nullable|integer|exists:t_FixedAssetClasses,Id',
            'LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'CapexBudget' => 'nullable|numeric|min:0',
            'Notes' => 'nullable|string',
        ]);
        CWIPProject::create($data);

        return redirect()->route('assets.acq.cwip-projects.index')->with('success', 'Project created.');
    }

    public function show(int $id)
    {
        $row = CWIPProject::findOrFail($id);
        $lines = CWIPLine::where('ProjectID', $id)->orderByDesc('CreatedOn')->paginate(20);

        return view('assets.acq.cwip.projects.show', compact('row', 'lines'));
    }

    public function edit(int $id)
    {
        $row = CWIPProject::findOrFail($id);
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = AssetLocation::orderBy('Site')->get();

        return view('assets.acq.cwip.projects.edit', compact('row', 'classes', 'locations'));
    }

    public function update(Request $request, int $id)
    {
        $row = CWIPProject::findOrFail($id);
        $data = $request->validate([
            'ProjectCode' => 'required|max:50|unique:t_CWIPProjects,ProjectCode,' . $row->Id . ',Id',
            'ProjectName' => 'required|max:200',
            'StartDate' => 'nullable|date',
            'EndDate' => 'nullable|date|after_or_equal:StartDate',
            'Status' => 'required|in:Open,Closed',
            'ClassID' => 'nullable|integer|exists:t_FixedAssetClasses,Id',
            'LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'CapexBudget' => 'nullable|numeric|min:0',
            'Notes' => 'nullable|string',
        ]);
        $row->update($data);

        return redirect()->route('assets.acq.cwip-projects.show', $row->Id)->with('success', 'Project updated.');
    }

    public function destroy(int $id)
    {
        CWIPProject::where('Id', $id)->delete();

        return redirect()->route('assets.acq.cwip-projects.index')->with('success', 'Project deleted.');
    }
}
