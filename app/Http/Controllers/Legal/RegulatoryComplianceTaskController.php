<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\RegulatoryComplianceTask;
use App\Models\Legal\RegulatoryObligation;
use Illuminate\Http\Request;

class RegulatoryComplianceTaskController extends Controller
{
    public function index($obligationId)
    {
        $obligation = RegulatoryObligation::findOrFail($obligationId);
        $tasks = $obligation->tasks;

        return view('legal.compliance.tasks.index', compact('obligation', 'tasks'));
    }

    public function create($obligationId)
    {
        $obligation = RegulatoryObligation::findOrFail($obligationId);

        return view('legal.compliance.tasks.create', compact('obligation'));
    }

    public function store(Request $request, $obligationId)
    {
        $validated = $request->validate([
            'TaskTitle' => 'required|string|max:200',
            'TaskDescription' => 'nullable|string',
            'TaskDueDate' => 'required|date',
            'TaskCompletedDate' => 'nullable|date',
            'TaskStatus' => 'required|string|max:50',
            'ResponsibleOfficer' => 'required|string|max:150',
            'EvidenceDocumentPath' => 'nullable|string|max:255',
        ]);

        $validated['ObligationID'] = $obligationId;

        RegulatoryComplianceTask::create($validated);

        return redirect()->route('legal.compliance.tasks.index', $obligationId)->with('success', 'Compliance task added.');
    }

    public function edit($obligationId, $id)
    {
        $task = RegulatoryComplianceTask::findOrFail($id);
        $obligation = RegulatoryObligation::findOrFail($obligationId);

        return view('legal.compliance.tasks.edit', compact('task', 'obligation'));
    }

    public function update(Request $request, $obligationId, $id)
    {
        $task = RegulatoryComplianceTask::findOrFail($id);

        $validated = $request->validate([
            'TaskTitle' => 'required|string|max:200',
            'TaskDescription' => 'nullable|string',
            'TaskDueDate' => 'required|date',
            'TaskCompletedDate' => 'nullable|date',
            'TaskStatus' => 'required|string|max:50',
            'ResponsibleOfficer' => 'required|string|max:150',
            'EvidenceDocumentPath' => 'nullable|string|max:255',
        ]);

        $task->update($validated);

        return redirect()->route('legal.compliance.tasks.index', $obligationId)->with('success', 'Task updated successfully.');
    }
}
