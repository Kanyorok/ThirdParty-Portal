<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Models\HRM\Committee;
use App\Models\HRM\Board;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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

    public function show(Committee $committee)
    {
        return view('hrms.committees.show', compact('committee'));
    }

    public function edit(Committee $committee)
    {
        return view('hrms.committees.show', compact('committee'));
    }

    public function store(Request $request)
    {
       // dd($request->all());
        $validated = $request->validate([
            'Name' => 'required|string|max:255',
            'Notes' => 'nullable|string',
            'Type' => 'nullable|string',
            'CommitteeID' => 'required|string|unique:t_Committees,CommitteeID',
        ]);
        $validated['CreatedBy'] = Auth::id();
        $validated['ModifiedBy'] = Auth::id();

            Committee::create($validated);

            return redirect()->route('hrms.committees.index')->with('success', 'Committee created successfully.');
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