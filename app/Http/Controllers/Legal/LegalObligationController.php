<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalObligation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LegalObligationController extends Controller
{
    public function index()
    {
        $obligations = LegalObligation::where('IsActive', 1)->orderByDesc('DueDate')->get();
        return view('legal.obligations.index', compact('obligations'));
    }

    public function create()
    {
        return view('legal.obligations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Title' => 'required|string|max:255',
            'LinkedType' => 'required|in:Contract,Case',
            'LinkedID' => 'required|integer',
            'DueDate' => 'required|date',
            'Status' => 'required|string',
            'Description' => 'nullable|string',
        ]);

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();
        $data['IsActive'] = 1;

        LegalObligation::create($data);

        return redirect()->route('legal.obligations.index')->with('success', 'Obligation created successfully.');
    }

    public function edit($id)
    {
        $obligation = LegalObligation::findOrFail($id);
        return view('legal.obligations.edit', compact('obligation'));
    }

    public function update(Request $request, $id)
    {
        $obligation = LegalObligation::findOrFail($id);

        $data = $request->validate([
            'Title' => 'required|string|max:255',
            'LinkedType' => 'required|in:Contract,Case',
            'LinkedID' => 'required|integer',
            'DueDate' => 'required|date',
            'Status' => 'required|string',
            'Description' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $obligation->update($data);

        return redirect()->route('legal.obligations.index')->with('success', 'Obligation updated successfully.');
    }

    public function markComplete($id)
    {
        $obligation = LegalObligation::findOrFail($id);
        $obligation->Status = 'Completed';
        $obligation->ModifiedBy = Auth::id();
        $obligation->ModifiedOn = now();
        $obligation->save();

        return redirect()->back()->with('success', 'Obligation marked as completed.');
    }



public function calendar()
{
    $obligations = LegalObligation::whereNull('DeletedOn')->get();

    $calendarEvents = $obligations->map(function ($obligation) {
        return [
            'title' => $obligation->ObligationTitle,
            'start' => $obligation->DueDate,
            'url' => route('legal.obligations.show', $obligation->ID),
        ];
    });

    return view('legal.obligations.calendar', compact('calendarEvents'));
}
}
