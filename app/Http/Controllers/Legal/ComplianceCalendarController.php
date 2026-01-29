<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceCalendarEntry;
use App\Models\Legal\ComplianceObligation;
use Illuminate\Http\Request;

class ComplianceCalendarController extends Controller
{
    public function index()
    {
        $entries = ComplianceCalendarEntry::with(['obligation', 'alerts'])->orderBy('StartDate', 'asc')->get();
        $owners = \DB::table('t_Users')->pluck('Name', 'Id'); // associative array

        return view('legal.compliance.calendar.index', compact('entries', 'owners'));
    }

    public function create()
    {
        $obligations = ComplianceObligation::orderBy('Title')->pluck('Title', 'Id');
        $owners = \DB::table('t_Users')->orderBy('Name')->pluck('Name', 'Id'); // ✅ add this

        return view('legal.compliance.calendar.create', compact('obligations', 'owners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'StartDate' => 'required|date',
            'EndDate' => 'nullable|date|after_or_equal:StartDate',
            'ObligationID' => 'nullable|exists:t_ComplianceObligations,Id',
            'OwnerID' => 'nullable|exists:t_Users,Id',
            'IsRecurring' => 'nullable|boolean',
            'RecurrenceType' => 'nullable|string|max:50',
        ]);

        ComplianceCalendarEntry::create($validated + [
                'CreatedBy' => auth()->id() ?? 1,   // ✅ logged-in user
                'CreatedOn' => now(),               // ✅ current datetime
            ]);

        ComplianceCalendarEntry::create($validated);

        return redirect()->route('legal.compliance.calendar.index')->with('success', 'Calendar entry created successfully.');
    }

    public function edit($id)
    {
        $entry = ComplianceCalendarEntry::findOrFail($id);
        $obligations = ComplianceObligation::orderBy('Title')->pluck('Title', 'Id');
        $owners = \DB::table('t_Users')->orderBy('Name')->pluck('Name', 'Id'); // ✅ add this

        return view('legal.compliance.calendar.edit', compact('entry', 'obligations', 'owners'));
    }

    public function update(Request $request, $id)
    {
        $entry = ComplianceCalendarEntry::findOrFail($id);

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'StartDate' => 'required|date',
            'EndDate' => 'nullable|date|after_or_equal:StartDate',
            'ObligationID' => 'nullable|exists:t_ComplianceObligations,Id',
            'OwnerID' => 'nullable|exists:t_Users,Id',
            'IsRecurring' => 'nullable|boolean',
            'RecurrenceType' => 'nullable|string|max:50',
        ]);

        $entry->update($validated + [
                'ModifiedBy' => auth()->id() ?? 1,  // ✅ track who edited
                'ModifiedOn' => now(),
            ]);

        return redirect()->route('legal.compliance.calendar.index')->with('success', 'Calendar entry updated successfully.');
    }

    public function calendar()
    {
        $entries = ComplianceCalendarEntry::select('id', 'Title as title', 'StartDate as start', 'EndDate as end', 'Description as description')
            ->get();

        return view('legal.compliance.calendar.calendar', ['calendarEntries' => $entries]);
    }

    public function show($id)
    {
        $entry = ComplianceCalendarEntry::with(['obligation', 'alerts'])->findOrFail($id);
        $owners = \DB::table('t_Users')->orderBy('Name')->pluck('Name', 'Id');

        return view('legal.compliance.calendar.show', compact('entry', 'owners'));
    }
}
