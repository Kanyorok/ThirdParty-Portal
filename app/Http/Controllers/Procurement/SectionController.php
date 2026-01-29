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

class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->authorize('viewAny', Section::class);
        $sections = Section::with('criteria')->get();

        return view('procurement.tendering.settings.sections', compact('sections'));
    }

    public function create(): View
    {
        $this->authorize('create', Section::class);
        $masterSections = Section::with('criteria')->get();
        $prequalificationRound = null;

        return view(
            'procurement.suppliers.prequalification.prequalification-rounds.create',
            compact('masterSections', 'prequalificationRound')
        );
    }

    public function store(Request $request, $sectionable = null): RedirectResponse
    {
        $this->authorize('create', Section::class);
        $validated = $request->validate([
            'SectionName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IsActive' => 'required|boolean',
            'criteria.*.CriteriaName' => 'required|string|max:255',
            'criteria.*.Description' => 'nullable|string',
            'criteria.*.IsActive' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $sectionData = [
                'SectionName' => $validated['SectionName'],
                'Description' => $validated['Description'] ?? null,
                'IsActive' => $validated['IsActive'],
            ];

            // Assign polymorphic relationship if sectionable provided
            if ($sectionable) {
                $sectionData['sectionable_id'] = $sectionable->Id;
                $sectionData['sectionable_type'] = get_class($sectionable);
            }

            $section = Section::create($sectionData);

            if (! empty($validated['criteria'])) {
                foreach ($validated['criteria'] as $criterion) {
                    $section->criteria()->create([
                        'CriteriaName' => $criterion['CriteriaName'],
                        'Description' => $criterion['Description'] ?? null,
                        'IsActive' => $criterion['IsActive'],
                    ]);
                }
            }

            activity()
                ->performedOn($section)
                ->causedBy(Auth::user())
                ->log('Created a new section with criteria: ' . $section->SectionName);

            DB::commit();

            return back()->with('success', 'Section and Criteria created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            activity()
                ->performedOn(new Section())
                ->causedBy(Auth::user())
                ->log('Failed to create section with criteria: ' . $th->getMessage());

            return back()->with('error', 'Failed to create section with criteria: ' . $th->getMessage());
        }
    }

    public function edit(Section $section)
    {
        $this->authorize('update', $section);
        $section->load('criteria');

        return view('procurement.tendering.settings.section-edit', compact('section'));
    }

    public function show(Section $section): View
    {
        $this->authorize('view', $section);
        $section->load('criteria');

        return view('procurement.tendering.settings.section-show', compact('section'));
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $this->authorize('update', $section);
        $validated = $request->validate([
            'SectionName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'IsActive' => 'required|boolean',
            'criteria.*.Id' => 'nullable|exists:t_Criterias,Id',
            'criteria.*.CriteriaName' => 'required|string|max:255',
            'criteria.*.Description' => 'nullable|string',
            'criteria.*.IsActive' => 'required|boolean',
            'criteria_remove' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $section->update([
                'SectionName' => $validated['SectionName'],
                'Description' => $validated['Description'] ?? null,
                'IsActive' => $validated['IsActive'],
            ]);

            if (! empty($validated['criteria_remove'])) {
                $idsToRemove = explode(',', $validated['criteria_remove']);
                Criteria::destroy($idsToRemove);
            }

            if (! empty($validated['criteria'])) {
                foreach ($validated['criteria'] as $criterionData) {
                    if (! empty($criterionData['Id'])) {
                        $criteria = Criteria::find($criterionData['Id']);
                        $criteria->update([
                            'CriteriaName' => $criterionData['CriteriaName'],
                            'Description' => $criterionData['Description'] ?? null,
                            'IsActive' => $criterionData['IsActive'],
                        ]);
                    } else {
                        $section->criteria()->create([
                            'CriteriaName' => $criterionData['CriteriaName'],
                            'Description' => $criterionData['Description'] ?? null,
                            'IsActive' => $criterionData['IsActive'],
                        ]);
                    }
                }
            }

            activity()
                ->performedOn($section)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old' => $section->getOriginal(),
                    'new' => $section->getChanges(),
                ])
                ->log('Updated section and criteria: ' . $section->SectionName);

            DB::commit();

            return redirect()->back()->with('success', 'Section and Criteria updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Failed to update section and criteria: ' . $th->getMessage());
        }
    }

    public function destroy(Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);
        DB::beginTransaction();

        try {
            $sectionName = $section->SectionName;
            $section->criteria()->delete();
            $section->delete();

            activity()
                ->performedOn($section)
                ->causedBy(Auth::user())
                ->withProperties(['section_name' => $sectionName])
                ->log('Deleted section and its criteria: ' . $sectionName);

            DB::commit();

            return redirect()->back()->with('success', 'Section and its criteria deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Failed to delete section: ' . $th->getMessage());
        }
    }
}
