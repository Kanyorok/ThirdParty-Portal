<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceIncident;
use App\Models\Legal\ComplianceIncidentAction;
use App\Models\Legal\ComplianceObligation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplianceIncidentController extends Controller
{
public function index()
{
    $incidents = ComplianceIncident::with(['obligation','severity'])
        ->orderBy('IncidentDate','desc')
        ->get();
    $owners = DB::table('t_Users')->pluck('Name','Id');

    // ✅ Summary counts
    $total = $incidents->count();
    $open = $incidents->where('Status','Open')->count();
    $resolved = $incidents->where('Status','Resolved')->count();
    $escalated = $incidents->where('Status','Escalated')->count();

    return view('legal.compliance.incidents.index', compact('incidents','owners','total','open','resolved','escalated'));
}

    public function create()
    {
        $obligations = ComplianceObligation::pluck('Title','Id');
        $severities = DB::table('t_IncidentSeverityLevels')->pluck('Name','Id');
        $owners = DB::table('t_Users')->pluck('Name','Id');
        return view('legal.compliance.incidents.create', compact('obligations','severities','owners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IncidentDate' => 'required|date',
            'ObligationID' => 'nullable|exists:t_ComplianceObligations,Id',
            'SeverityID' => 'required|exists:t_IncidentSeverityLevels,Id',
            'ResponsibleUserID' => 'nullable|exists:t_Users,Id',
        ]);

        ComplianceIncident::create($validated + [
            'Status' => 'Open',
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.incidents.index')->with('success','Incident logged successfully.');
    }

    public function show($id)
    {
        $incident = ComplianceIncident::with(['obligation','severity','actions'])->findOrFail($id);
        $owners = DB::table('t_Users')->pluck('Name','Id');
        return view('legal.compliance.incidents.show', compact('incident','owners'));
    }

    public function edit($id)
    {
        $incident = ComplianceIncident::findOrFail($id);
        $obligations = ComplianceObligation::pluck('Title','Id');
        $severities = DB::table('t_IncidentSeverityLevels')->pluck('Name','Id');
        $owners = DB::table('t_Users')->pluck('Name','Id');
        return view('legal.compliance.incidents.edit', compact('incident','obligations','severities','owners'));
    }

    public function update(Request $request, $id)
    {
        $incident = ComplianceIncident::findOrFail($id);

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IncidentDate' => 'required|date',
            'ObligationID' => 'nullable|exists:t_ComplianceObligations,Id',
            'SeverityID' => 'required|exists:t_IncidentSeverityLevels,Id',
            'ResponsibleUserID' => 'nullable|exists:t_Users,Id',
            'Status' => 'required|string|max:50',
        ]);

        $incident->update($validated + [
            'ModifiedBy' => auth()->id() ?? 1,
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.incidents.show',$id)->with('success','Incident updated successfully.');
    }

    public function addAction(Request $request, $id)
    {
        $request->validate([
            'RootCause' => 'nullable|string',
            'CorrectiveAction' => 'required|string',
            'ActionOwnerID' => 'nullable|exists:t_Users,Id',
            'DueDate' => 'nullable|date',
        ]);

        ComplianceIncidentAction::create([
            'IncidentID' => $id,
            'RootCause' => $request->RootCause,
            'CorrectiveAction' => $request->CorrectiveAction,
            'ActionOwnerID' => $request->ActionOwnerID,
            'DueDate' => $request->DueDate,
            'Status' => 'Pending',
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return back()->with('success','Corrective action added successfully.');
    }

    public function dashboard()
{
    // Counts by severity
    $severityData = \DB::table('t_ComplianceIncidents')
        ->join('t_IncidentSeverityLevels','t_ComplianceIncidents.SeverityID','=','t_IncidentSeverityLevels.Id')
        ->select('t_IncidentSeverityLevels.Name as severity', \DB::raw('COUNT(*) as total'))
        ->groupBy('t_IncidentSeverityLevels.Name')
        ->pluck('total','severity');

    // Counts by status
    $statusData = \DB::table('t_ComplianceIncidents')
        ->select('Status', \DB::raw('COUNT(*) as total'))
        ->groupBy('Status')
        ->pluck('total','Status');

    // Quick stats
    $total = \DB::table('t_ComplianceIncidents')->count();
    $open = \DB::table('t_ComplianceIncidents')->where('Status','Open')->count();
    $resolved = \DB::table('t_ComplianceIncidents')->where('Status','Resolved')->count();
    $escalated = \DB::table('t_ComplianceIncidents')->where('Status','Escalated')->count();

    return view('legal.compliance.incidents.dashboard', compact(
        'severityData','statusData','total','open','resolved','escalated'
    ));
}

}
