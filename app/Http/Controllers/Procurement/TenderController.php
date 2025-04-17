<?php

namespace App\Http\Controllers\Procurement;

use App\Models\Procurement\Tender;
use App\Models\Procurement\ProcurementMode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenders = Tender::with('procurementMode')->get();
        return view('procurement.tenders.index', compact('tenders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $procurementModes = ProcurementMode::all();
        $currencies = config('app.currencies'); // Assuming you have a config file for currencies

        return view('procurement.tenders.create', compact('procurementModes', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ProcurementModeId' => 'required|exists:t_ProcurementModes,id',
            'EstimatedValue' => 'required|numeric|min:0',
            'Currency' => 'required|string|max:3',
            'StartDate' => 'required|date|after_or_equal:today',
        ]);

        $tender = new Tender();
        $tender->TenderNumber = 'TNDR-' . Str::upper(Str::random(8)); // Generate a unique tender number
        $tender->Title = $request->Title;
        $tender->Description = $request->Description;
        $tender->ProcurementModeId = $request->ProcurementModeId;
        $tender->EstimatedValue = $request->EstimatedValue;
        $tender->Currency = $request->Currency;
        $tender->StartDate = $request->StartDate;
        $tender->Status = 'Open'; // Default status
        $tender->CreatedBy = Auth::id(); // Assuming you have authentication set up
        $tender->ModifiedBy = Auth::id();
        $tender->save();

        return redirect()->route('tendering-process.index')->with('success', 'Tender created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
