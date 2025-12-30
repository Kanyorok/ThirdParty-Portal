<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Section;
use App\Models\Procurement\Criteria;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PrequalificationRoundSetupController extends Controller
{
    /**
     * Display the sections and criteria for a specific prequalification round.
     *
     * @param PrequalificationRound $prequalificationRound
     * @return \Illuminate\View\View
     */
    public function show(PrequalificationRound $prequalificationRound): View
    {
        // Eager load sections and criteria to prevent N+1 queries ~ efficiency
        $sections = $prequalificationRound->prequalificationSections()
            ->with(['masterSection', 'criteria.masterCriteria'])
            ->get();

        // Get configured sections and criteria for this round
        // Pass master sections and criteria for the add forms
        $masterSections = Section::all();
        $masterCriteria = Criteria::all();

        return view('procurement.prequalification.setup', compact('prequalificationRound', 'sections', 'masterSections', 'masterCriteria'));
    }

    /**
     * Store the criteria setup for a specific prequalification round.
     *
     * @param Request $request
     * @param PrequalificationRound $prequalificationRound
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, PrequalificationRound $prequalificationRound): RedirectResponse
    {
        $validatedData = $request->validate([
            'sections' => ['nullable', 'array'],
            'sections.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.included' => ['boolean'],
        ]);

        // Enforce: total section weights must equal 100 (tolerance 0.01)
        $totalWeight = collect($validatedData['sections'] ?? [])->sum(function ($s) { return (float)($s['weight'] ?? 0); });
        if (abs($totalWeight - 100.0) > 0.01) {
            return back()->withInput()->withErrors(['sections' => 'Total section weight must equal 100%. Current total is '.number_format($totalWeight,2).'%.']);
        }

        // Enforce: each selected section must have at least one included criterion
        $sectionsSubmitted = array_keys($validatedData['sections'] ?? []);
        $criteriaSubmitted = $validatedData['criteria'] ?? [];
        $criteriaBySection = [];
        foreach ($criteriaSubmitted as $criteriaId => $c) {
            // Need to map criteriaId -> SectionId; fetch minimal map once
            $criteriaBySection[$criteriaId] = $criteriaBySection[$criteriaId] ?? null;
        }
        if (!empty($sectionsSubmitted)) {
            // Build CriteriaId -> SectionId map for submitted criteria set
            $map = DB::table('t_Criterias as c')
                ->select('c.Id as CriteriaId','c.SectionID as SectionId')
                ->whereIn('c.Id', array_keys($criteriaSubmitted))
                ->pluck('SectionId','CriteriaId');

            $errors = [];
            foreach ($sectionsSubmitted as $sectionId) {
                $hasIncluded = false;
                foreach ($criteriaSubmitted as $criteriaId => $payload) {
                    $included = (bool)($payload['included'] ?? false);
                    if ($included && (int)($map[$criteriaId] ?? 0) === (int)$sectionId) {
                        $hasIncluded = true;
                        break;
                    }
                }
                if (!$hasIncluded) {
                    $errors["sections.$sectionId"] = 'At least one criterion must be included for this section.';
                }
            }
            if (!empty($errors)) {
                return back()->withInput()->withErrors($errors);
            }
        }

        DB::beginTransaction();

        try {
            // Sync sections, automatically attaching or detaching based on the form data
            // The sync method works well here for a "save all at once" scenario.
            $sectionsToSync = [];
            if (isset($validatedData['sections'])) {
                foreach ($validatedData['sections'] as $sectionId => $sectionData) {
                    $sectionsToSync[$sectionId] = ['Weight' => $sectionData['weight']];
                }
            }
            // Use the relationship method defined in the PrequalificationRound model
            $prequalificationRound->prequalificationSections()->sync($sectionsToSync);

            // Sync criteria, handling the 'included' status
            $criteriaToSync = [];
            if (isset($validatedData['criteria'])) {
                foreach ($validatedData['criteria'] as $criteriaId => $criteriaData) {
                    $criteriaToSync[$criteriaId] = ['Included' => $criteriaData['included'] ?? false];
                }
            }
            // Use the relationship method defined in the PrequalificationRound model
            $prequalificationRound->prequalificationCriteria()->sync($criteriaToSync);

            DB::commit();

            return redirect()->route('prequalification.rounds.show', $prequalificationRound)
                ->with('success', 'Prequalification criteria configured successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to configure criteria: ' . $e->getMessage());
        }
    }
}
