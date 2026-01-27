<?php

namespace App\Http\Controllers\Assets\Acq;

use App\Http\Controllers\Controller;
use App\Models\Assets\Acq\{CWIPLine, CWIPProject};
use App\Models\Assets\Acq\ProcurementDoc;
use App\Models\Assets\Settings\{AssetBook, AssetLocation, FixedAssetClass};
use Illuminate\Http\Request;

class CWIPLineController extends Controller
{
    public function index(int $project)
    {
        $proj = CWIPProject::findOrFail($project);
        $rows = CWIPLine::where('ProjectID', $project)->orderByDesc('CreatedOn')->paginate(20);

        return view('assets.acq.cwip.lines.index', compact('proj', 'rows'));
    }

    public function create(int $project)
    {
        $proj = CWIPProject::findOrFail($project);
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = AssetLocation::orderBy('Site')->get();
        $books = AssetBook::orderBy('Name')->get();
        $proc = ProcurementDoc::orderByDesc('DocDate')->limit(50)->get();

        return view('assets.acq.cwip.lines.create', compact('proj', 'classes', 'locations', 'books', 'proc'));
    }

    public function store(Request $request, int $project)
    {
        $data = $request->validate([
            'RefType' => 'nullable|in:PO,GRN,MANUAL',
            'RefID' => 'nullable|integer|exists:t_ProcurementDocs,Id',
            'Description' => 'required|max:250',
            'Quantity' => 'required|numeric|min:0.0001',
            'UnitCost' => 'required|numeric|min:0',
            'TaxAmount' => 'nullable|numeric|min:0',
            'ReceivedDate' => 'nullable|date',
            'ClassID' => 'nullable|integer|exists:t_FixedAssetClasses,Id',
            'LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'BookID' => 'nullable|integer|exists:t_AssetBooks,Id',
            'IsCapitalizable' => 'nullable|boolean',
            'Notes' => 'nullable|max:500',
        ]);
        $data['ProjectID'] = $project;
        $data['IsCapitalizable'] = $request->boolean('IsCapitalizable', true);
        CWIPLine::create($data);

        return redirect()->route('assets.acq.cwip-projects.lines.index', $project)->with('success', 'CWIP line added.');
    }

    public function edit(int $project, int $id)
    {
        $proj = CWIPProject::findOrFail($project);
        $row = CWIPLine::where('ProjectID', $project)->findOrFail($id);
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = AssetLocation::orderBy('Site')->get();
        $books = \App\Models\Assets\Settings\AssetBook::orderBy('Name')->get();

        return view('assets.acq.cwip.lines.edit', compact('proj', 'row', 'classes', 'locations', 'books'));
    }

    public function update(Request $request, int $project, int $id)
    {
        $row = CWIPLine::where('ProjectID', $project)->findOrFail($id);
        $data = $request->validate([
            'Description' => 'required|max:250',
            'Quantity' => 'required|numeric|min:0.0001',
            'UnitCost' => 'required|numeric|min:0',
            'TaxAmount' => 'nullable|numeric|min:0',
            'ReceivedDate' => 'nullable|date',
            'ClassID' => 'nullable|integer|exists:t_FixedAssetClasses,Id',
            'LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'BookID' => 'nullable|integer|exists:t_AssetBooks,Id',
            'IsCapitalizable' => 'nullable|boolean',
            'Notes' => 'nullable|max:500',
        ]);
        $data['IsCapitalizable'] = $request->boolean('IsCapitalizable', true);
        $row->update($data);

        return back()->with('success', 'CWIP line updated.');
    }

    public function destroy(int $project, int $id)
    {
        CWIPLine::where('ProjectID', $project)->where('Id', $id)->delete();

        return back()->with('success', 'CWIP line deleted.');
    }
}
