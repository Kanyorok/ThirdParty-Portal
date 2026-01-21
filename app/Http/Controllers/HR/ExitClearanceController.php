<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitClearance;
use App\Models\HR\Exit\ExitRequest;
use Illuminate\Http\Request;

class ExitClearanceController extends Controller
{
    public function index(Request $request)
    {
        $query = ExitClearance::with(['exit.employee', 'department'])
            ->orderByDesc('Id');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $clearances = $query->paginate(30);

        return view('hr.exit.clearances.index', compact('clearances'));
    }

    public function update(Request $request, $exitId, $clearanceId)
    {
        $exit = ExitRequest::findOrFail($exitId);
        $clearance = ExitClearance::where('ExitID', $exit->Id)->where('Id', $clearanceId)->firstOrFail();

        $data = $request->validate([
            'Status' => ['required', 'string', 'max:30'],
            'Remarks' => ['nullable', 'string', 'max:255'],
            'Details' => ['nullable', 'string'],
        ]);

        $clearance->update([
            'Status' => $data['Status'],
            'Remarks' => $data['Remarks'] ?? null,
            'Details' => $data['Details'] ?? null,
            'ClearedBy' => auth()->id(),
            'ClearedOn' => $data['Status'] === 'Cleared' ? now() : null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.exit.requests.clearances', $exit->Id)->with('success', 'Clearance updated.');
    }
}
