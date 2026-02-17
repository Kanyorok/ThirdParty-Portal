<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitRedundancy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExitRedundancyController extends Controller
{
    public function index(Request $request)
    {
        $query = ExitRedundancy::orderByDesc('CreatedOn');
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $redundancies = $query->paginate(30);
        return view('hr.exit.redundancies.index', compact('redundancies'));
    }

    public function create()
    {
        return view('hr.exit.redundancies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Reason' => ['nullable', 'string', 'max:150'],
            'Criteria' => ['nullable', 'string'],
            'SelectionMethod' => ['nullable', 'string', 'max:100'],
            'UnionNotified' => ['sometimes', 'boolean'],
            'UnionNotifiedOn' => ['nullable', 'date'],
            'LabourOfficeNotified' => ['sometimes', 'boolean'],
            'LabourOfficeNotifiedOn' => ['nullable', 'date'],
            'Notes' => ['nullable', 'string'],
        ]);

        $redundancy = ExitRedundancy::create([
            'RefNo' => $this->generateRefNo(),
            'Reason' => $data['Reason'] ?? null,
            'Criteria' => $data['Criteria'] ?? null,
            'SelectionMethod' => $data['SelectionMethod'] ?? null,
            'UnionNotified' => $request->boolean('UnionNotified', false),
            'UnionNotifiedOn' => $data['UnionNotifiedOn'] ?? null,
            'LabourOfficeNotified' => $request->boolean('LabourOfficeNotified', false),
            'LabourOfficeNotifiedOn' => $data['LabourOfficeNotifiedOn'] ?? null,
            'Notes' => $data['Notes'] ?? null,
            'Status' => 'Draft',
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.exit.redundancies.show', $redundancy->Id)->with('success', 'Redundancy record saved.');
    }

    public function show($id)
    {
        $redundancy = ExitRedundancy::findOrFail($id);
        return view('hr.exit.redundancies.show', compact('redundancy'));
    }

    public function approve($id)
    {
        $redundancy = ExitRedundancy::findOrFail($id);
        if ($redundancy->Status === 'Approved') {
            return redirect()->route('hr.exit.redundancies.show', $redundancy->Id)->with('success', 'Redundancy already approved.');
        }

        $redundancy->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.exit.redundancies.show', $redundancy->Id)->with('success', 'Redundancy approved.');
    }

    private function generateRefNo(): string
    {
        return 'RED-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }
}
