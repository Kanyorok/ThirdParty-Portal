<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CriteriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Section $section): View
    {
        $criterias = $section->criteria;
        return view('procurement.tendering.settings.criterias', compact('criterias', 'section'));
    }

    public function store(Request $request, Section $section): RedirectResponse
    {
        // CORRECTED: Validation rules now match the model's fillable field names
        $validated = $request->validate([
            'CriteriaName' => 'required|string|max:255',
            'Description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $criteria = $section->criteria()->create([
                'CriteriaName' => $validated['CriteriaName'],
                'Description' => $validated['Description'] ?? null,
            ]);

            activity()
                ->performedOn($criteria)
                ->causedBy(Auth::user())
                ->log('Created a new criteria: ' . $validated['CriteriaName']);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Failed to create criteria: ' . $th->getMessage());
        }

        return back()->with('success', 'Criteria created successfully!');
    }

    public function update(Request $request, Section $section, Criteria $criteria): RedirectResponse
    {
        // CORRECTED: Validation rules now match the model's fillable field names
        $validated = $request->validate([
            'CriteriaName' => 'required|string|max:255',
            'Description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $criteria->getOriginal();
            // CORRECTED: The assignment now uses the correct validated keys
            $criteria->CriteriaName = $validated['CriteriaName'];
            $criteria->Description = $validated['Description'] ?? null;
            $criteria->save();

            activity()
                ->performedOn($criteria)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old' => $oldValues,
                    'new' => $criteria->getChanges(),
                ])
                ->log('Updated criteria: ' . $criteria->CriteriaName);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Failed to update criteria: ' . $th->getMessage());
        }

        return redirect()->back()->with('success', 'Criteria updated successfully!');
    }

    public function destroy(Section $section, Criteria $criteria): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $criteriaName = $criteria->CriteriaName;
            $criteria->delete();

            activity()
                ->performedOn($criteria)
                ->causedBy(Auth::user())
                ->withProperties(['criteria_name' => $criteriaName])
                ->log('Deleted criteria: ' . $criteriaName);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete criteria: ' . $th->getMessage());
        }

        return redirect()->back()->with('success', 'Criteria deleted successfully.');
    }

    public function fetchAll(Section $section): \Illuminate\Http\JsonResponse
    {
        return response()->json($section->criteria()->select('Id', 'CriteriaName', 'Description')->get());
    }
}
