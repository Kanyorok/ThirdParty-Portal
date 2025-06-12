<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Criteria::create([
                'CriteriaName' => $request->input('name'),
                'SectionID' => $request->input('section_id'), // Ensure section_id is provided in the request
                'Description' => $request->input('desc', null), // Default to null if not provided
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);
            // Log the action
            activity()
                ->performedOn(new Criteria())
                ->causedBy(auth()->id())
                ->log('Created a new criteria: ' . $request->input('name'));
            DB::commit();
            return back()->with('success', 'Criteria created successfully: ');
        } catch (\Throwable $th) {
            DB::rollBack();
            // Log the error
            return $th->getMessage();
            activity()
                ->performedOn(new Criteria())
                ->causedBy(auth()->id())
                ->log('Error creating criteria: ' . $th->getMessage());
            return back()->withErrors(['error' => 'Failed to create criteria: ']);
        }
        return back()->with('success', 'Criteria created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sectionID = $id;
        $criterias = Criteria::where('SectionId', $id)->get();
        return view('procurement.tendering.settings.criterias', compact('criterias', 'sectionID'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $criteria = Criteria::findOrFail($id);
        $oldValues = $criteria->getOriginal();
        $criteria->CriteriaName = $request->name;
        $criteria->Description = $request->desc;
        $criteria->ModifiedBy = auth()->id();
        $criteria->ModifiedOn = now();
        $criteria->save();

        activity()
            ->performedOn($criteria)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $criteria->getChanges()
            ])
            ->log('Updated criteria: ' . $criteria->CriteriaName);

        return redirect()->back()->with('success', 'Criteria updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $criteria = Criteria::findOrFail($id);
        $criteriaName = $criteria->CriteriaName;
        $criteria->DeletedBy = auth()->id();

        activity()
            ->performedOn($criteria)
            ->causedBy(auth()->user())
            ->withProperties(['criteria_name' => $criteriaName])
            ->log('Deleted criteria: ' . $criteriaName);

        $criteria->delete();

        return redirect()->back()->with('success', 'Criteria deleted successfully.');
    }
}
