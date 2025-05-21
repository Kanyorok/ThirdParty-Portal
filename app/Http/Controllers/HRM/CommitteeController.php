<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Models\HRM\Committee;
use App\Models\HRM\Board;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Board\CommitteeRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    public function store(Request $request): JsonResponse
    {
        // $this->authorize('viewAny', Committee::class);
        $actor = $request->user();

        $validated = $request->validate([
            'CommitteeName'  => 'required|string|max:200|unique:t_Committees,Name',
            'CommitteeNotes' => 'nullable|string|max:2000',
            'CommitteeType'  => 'required|string|max:50',
        ]);

        try {
            // Generate custom CommitteeID (e.g., COMM-001)
            $count = Committee::withTrashed()->count();
            do {
                $count++;
                $generatedID = strtoupper('COMM-' . str_pad($count, 3, '0', STR_PAD_LEFT));
            } while (Committee::withTrashed()->where('CommitteeID', $generatedID)->exists());

            // Create committee using Eloquent model
            $committee = Committee::create([
                'CommitteeID' => $generatedID,
                'Name'        => $validated['CommitteeName'],
                'Notes'       => $validated['CommitteeNotes'] ?? null,
                'Type'        => $validated['CommitteeType'],
                'CreatedBy'   => $actor->Id,
                'ModifiedBy'  => $actor->Id,
                'CreatedOn'   => Carbon::now(),
                'ModifiedOn'  => Carbon::now(),
            ]);

            // Log activity
            activity()
                ->causedBy($actor)
                ->performedOn($committee)
                ->event('create')
                ->log('Created board committee ' . $committee->CommitteeID . '.');
        } catch (Exception | \Throwable $e) {
            Log::error('Error creating committee: ' . $e->getMessage(), ['exception' => $e]);
            return $this->errored('An unexpected error occurred: ' . $e->getMessage());
        }

        return $this->succeeded('Committee added successfully.');
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