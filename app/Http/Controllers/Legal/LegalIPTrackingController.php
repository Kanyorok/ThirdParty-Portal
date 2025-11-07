<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalIPTracking;
use App\Models\Legal\LegalIntellectualProperty;
use Illuminate\Support\Facades\Auth;

class LegalIPTrackingController extends Controller
{
    public function index()
    {
        $trackings = LegalIPTracking::with('ip')->orderByDesc('TrackingDate')->get();
        return view('legal.ip_tracking.index', compact('trackings'));
    }

    public function create()
    {
        $ips = LegalIntellectualProperty::where('IsActive', 1)->get();
        return view('legal.ip_tracking.create', compact('ips'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'IPID' => 'required|integer',
            'TrackingType' => 'required|string',
            'TrackingDate' => 'required|date',
            'Status' => 'required|string',
            'Remarks' => 'nullable|string',
        ]);

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();

        LegalIPTracking::create($data);

        return redirect()->route('legal.ip_tracking.index')->with('success', 'Tracking record created successfully.');
    }

    public function edit($id)
    {
        $tracking = LegalIPTracking::findOrFail($id);
        $ips = LegalIntellectualProperty::where('IsActive', 1)->get();
        return view('legal.ip_tracking.edit', compact('tracking', 'ips'));
    }

    public function update(Request $request, $id)
    {
        $tracking = LegalIPTracking::findOrFail($id);

        $data = $request->validate([
            'IPID' => 'required|integer',
            'TrackingType' => 'required|string',
            'TrackingDate' => 'required|date',
            'Status' => 'required|string',
            'Remarks' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $tracking->update($data);

        return redirect()->route('legal.ip_tracking.index')->with('success', 'Tracking record updated.');
    }
}
