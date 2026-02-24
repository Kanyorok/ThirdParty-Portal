<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CriteriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Section $section): View
    {
        $this->authorize('viewAny', Criteria::class);
        $criterias = $section->criteria;

        return view('procurement.tendering.settings.criterias', compact('criterias', 'section'));
    }

    public function store(Request $request, Section $section): RedirectResponse
    {
        $this->authorize('create', Criteria::class);
        // CORRECTED: Validation rules now match the model's fillable field names
        $validated = $request->validate([
            'CriteriaName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IsActive' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $criteria = $section->criteria()->create([
                'CriteriaName' => $validated['CriteriaName'],
                'Description' => $validated['Description'] ?? null,
                'IsActive' => (int) $validated['IsActive'],
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

    public function update(Request $request, Section $section, Criteria $criterion): RedirectResponse
    {
        abort_unless((int) $criterion->SectionID === (int) $section->Id, 404);
        $this->authorize('update', $criterion);
        // CORRECTED: Validation rules now match the model's fillable field names
        $validated = $request->validate([
            'CriteriaName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IsActive' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $oldValues = $criterion->getOriginal();
            // CORRECTED: The assignment now uses the correct validated keys
            $criterion->CriteriaName = $validated['CriteriaName'];
            $criterion->Description = $validated['Description'] ?? null;
            $criterion->IsActive = (int) $validated['IsActive'];
            $criterion->save();

            activity()
                ->performedOn($criterion)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old' => $oldValues,
                    'new' => $criterion->getChanges(),
                ])
                ->log('Updated criteria: ' . $criterion->CriteriaName);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Failed to update criteria: ' . $th->getMessage());
        }

        return redirect()->back()->with('success', 'Criteria updated successfully!');
    }

    public function destroy(Section $section, Criteria $criterion): RedirectResponse
    {
        abort_unless((int) $criterion->SectionID === (int) $section->Id, 404);
        $this->authorize('delete', $criterion);
        DB::beginTransaction();

        try {
            $criteriaName = $criterion->CriteriaName;
            $criterion->delete();

            activity()
                ->performedOn($criterion)
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
        $this->authorize('viewAny', Criteria::class);

        return response()->json($section->criteria()->select('Id', 'CriteriaName', 'Description')->get());
    }
}
