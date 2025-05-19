<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Models\HRM\Committee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Board\CommitteeRequest;
use Exception;
use Illuminate\Support\Facades\DB;

class CommitteeController extends Controller
{
    public function index()
    {
        $committees = Committee::all();
        return view('hrms.committees.index', compact('committees'));
    }

    public function create()
    {
        return view('hrms.committees.create');
    }

    public function store(CommitteeRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Board::class);
        $actor = $request->user();
        try {
            DB::transaction(function () use ($actor, $request) {
                $committee = Committee::create([
                                                "CommitteeID" => $request->generateID(),
                                                "Name"        => $request->validated('CommitteeName'),
                                                'Notes'       => $request->validated('CommitteeNotes'),
                                                'CreatedBy'   => $actor->Id,
                                                'ModifiedBy'  => $actor->Id,
                                               ]);

                activity()->causedBy($actor)->performedOn($committee)->event('create')->log('created board committee ' . $committee->CommitteeID . '.');
            });
        } catch (Exception | \Throwable $e) {
            Log::error('creating committee.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('committee added successfully');
    }

    public function show(Committee $committee)
    {
        return view('hrms.committees.show', compact('committee'));
    }

    public function edit(Committee $committee)
    {
        return view('hrms.committees.edit', compact('committee'));
    }

    public function update(Request $request, Committee $committee)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:255',
            'Notes' => 'nullable|string',
        ]);

        // Auto-fill ModifiedBy (from session or fallback)
        $validated['ModifiedBy'] = auth()->user()->name ?? 'system';

        $committee->update($validated);

        return redirect()->route('hrms.committees.index')->with('success', 'Committee updated successfully.');
    }

    public function destroy(Committee $committee)
    {
        $committee->delete();

        return redirect()->route('hrms.committees.index')->with('success', 'Committee deleted successfully.');
    }
}
