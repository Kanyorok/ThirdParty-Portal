<?php


namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceControl;
use App\Models\Legal\ComplianceControlEvidence;
use App\Models\Legal\ComplianceObligation;
use App\Models\Legal\ComplianceArea;
use App\Models\Legal\ControlType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplianceControlController extends Controller
{
    public function index()
    {
        $controls = ComplianceControl::with(['area','controlType'])->get();
        return view('legal.compliance.controls.index', compact('controls'));
    }

    public function create()
    {
        $areas = ComplianceArea::pluck('Name','Id');
        $types = ControlType::pluck('Name','Id');
        $owners = DB::table('t_Users')->pluck('Name','Id');
        $obligations = ComplianceObligation::pluck('Title','Id');
        return view('legal.compliance.controls.create', compact('areas','types','owners','obligations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ComplianceAreaID' => 'nullable|exists:t_ComplianceAreas,Id',
            'ControlTypeID' => 'nullable|exists:t_ControlTypes,Id',
            'OwnerID' => 'nullable|exists:t_Users,Id',
            'Obligations' => 'array|nullable',
        ]);

        $control = ComplianceControl::create($validated + [
            'IsActive' => 1,
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        if($request->has('Obligations')){
            $control->obligations()->sync($request->Obligations);
        }

        return redirect()->route('legal.compliance.controls.show',$control->Id)
            ->with('success','Control created successfully.');
    }

    public function show($id)
    {
        $control = ComplianceControl::with(['area','controlType','obligations','evidence'])->findOrFail($id);
        return view('legal.compliance.controls.show', compact('control'));
    }

    public function edit($id)
    {
        $control = ComplianceControl::findOrFail($id);
        $areas = ComplianceArea::pluck('Name','Id');
        $types = ControlType::pluck('Name','Id');
        $owners = DB::table('t_Users')->pluck('Name','Id');
        $obligations = ComplianceObligation::pluck('Title','Id');
        return view('legal.compliance.controls.edit', compact('control','areas','types','owners','obligations'));
    }

    public function update(Request $request, $id)
    {
        $control = ComplianceControl::findOrFail($id);

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ComplianceAreaID' => 'nullable|exists:t_ComplianceAreas,Id',
            'ControlTypeID' => 'nullable|exists:t_ControlTypes,Id',
            'OwnerID' => 'nullable|exists:t_Users,Id',
            'Obligations' => 'array|nullable',
        ]);

        $control->update($validated + [
            'ModifiedBy' => auth()->id() ?? 1,
            'ModifiedOn' => now(),
        ]);

        if($request->has('Obligations')){
            $control->obligations()->sync($request->Obligations);
        }

        return redirect()->route('legal.compliance.controls.show',$id)
            ->with('success','Control updated successfully.');
    }

    public function destroy($id)
    {
        $control = ComplianceControl::findOrFail($id);
        $control->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id() ?? 1,
            'DeletedOn' => now(),
        ]);
        return redirect()->route('legal.compliance.controls.index')
            ->with('success','Control deactivated.');
    }

    public function uploadEvidence(Request $request, $id)
    {
        $request->validate([
            'evidence' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
        ]);

        $control = ComplianceControl::findOrFail($id);
        $file = $request->file('evidence');
        $latestVersion = ComplianceControlEvidence::where('ControlID',$id)->max('Version') ?? 0;
        $version = $latestVersion + 1;
        $path = $file->store('compliance/controls');

        ComplianceControlEvidence::create([
            'ControlID' => $id,
            'FileName' => $file->getClientOriginalName(),
            'MimeType' => $file->getMimeType(),
            'FilePath' => $path,
            'Version' => $version,
            'UploadedBy' => auth()->id() ?? 1,
            'UploadedOn' => now(),
        ]);

        return back()->with('success','Evidence uploaded successfully.');
    }
}
