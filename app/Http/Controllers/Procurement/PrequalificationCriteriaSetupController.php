<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\PrequalificationPeriodEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\PrequalificationCriteria;
use App\Models\Procurement\PrequalificationPeriod;
use App\Models\Procurement\PrequalificationSection;
use App\Models\procurement\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrequalificationCriteriaSetupController extends Controller
{
    //
    public function index()
    {
        // Join the relevant data for display
        $sections = PrequalificationSection::with(['section', 'round'])
            ->orderByDesc('CreatedOn')
            ->get();

        return view('procurement.suppliers.prequalification.criteria.index', compact('sections'));
    }

    public function create()
    {
        $rounds = PrequalificationPeriod::where('Status', PrequalificationPeriodEnum::Open)->get();
        $sections = Section::all();
        return view('procurement.suppliers.prequalification.criteria.create', compact('sections', 'rounds'));
    }

    public function getCriteriabySection($round_id, $section_id)
    {
        $round = PrequalificationPeriod::findOrFail($round_id);
        $section = Section::findOrFail($section_id);

        // Filter only criteria for the selected section
        $criterias = Criteria::where('SectionId', $section_id)->get();

        return view('procurement.suppliers.prequalification.criteria.criteria', compact('criterias', 'round', 'section'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'round_id' => 'required|exists:t_PrequalificationPeriod,Id',
            'sections' => 'required|array',
            'weights' => 'required|array',
        ]);

        $roundId = $request->input('round_id');
        $sectionIds = $request->input('sections');
        $weights = $request->input('weights');

        // Ensure total weight of selected sections equals 100
        $totalWeight = 0;
        foreach ($sectionIds as $sectionId) {
            $weight = isset($weights[$sectionId]) ? floatval($weights[$sectionId]) : 0;
            $totalWeight += $weight;
        }

        if ($totalWeight !== 100.0) {
            return back()->withInput()->withErrors([
                'weights' => "The total weight of selected sections must be 100%. Currently it adds up to {$totalWeight}%.",
            ]);
        }

        foreach ($sectionIds as $sectionId) {
            $weight = isset($weights[$sectionId]) ? floatval($weights[$sectionId]) : 0;

            // Save or update section entry
            $section = PrequalificationSection::updateOrCreate(
                [
                    'RoundId' => $roundId,
                    'SectionId' => $sectionId,
                ],
                [
                    'Weight' => $weight,
                    'CreatedBy' => auth()->id(),
                    'ModifiedBy' => auth()->id(),
                ]
            );

            // Attach criteria for that section
            $criteriaItems = Criteria::where('SectionId', $sectionId)->get();

            foreach ($criteriaItems as $criteria) {
                PrequalificationCriteria::firstOrCreate([
                    'RoundId' => $roundId,
                    'SectionId' => $sectionId,
                    'CriteriaId' => $criteria->id,
                ], [
                    'Included' => true,
                    'CreatedBy' => auth()->id(),
                    'ModifiedBy' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('preqcriteria.index')->with('success', 'Sections and Criteria have been assigned successfully.');
    }

    public function edit($roundId)
    {
        $round = PrequalificationPeriod::findOrFail($roundId);
        $sections = Section::with('criteria')->get();
        $selectedSections = PrequalificationSection::where('RoundId', $roundId)->get();

        return view('procurement.suppliers.prequalification.criteria.edit', compact('round', 'sections', 'selectedSections'));
    }


    public function update(Request $request, $roundId)
    {
        $request->validate([
            'round_id' => 'required|exists:t_PrequalificationPeriod,Id',
            'sections' => 'required|array',
            'weights' => 'required|array',
        ]);

        $sectionIds = $request->input('sections');
        $weights = $request->input('weights');

        $totalWeight = 0;
        foreach ($sectionIds as $sectionId) {
            $totalWeight += isset($weights[$sectionId]) ? floatval($weights[$sectionId]) : 0;
        }

        if ($totalWeight !== 100.0) {
            return back()->withInput()->withErrors([
                'weights' => "The total weight of selected sections must be 100%. Currently it adds up to {$totalWeight}%.",
            ]);
        }

        // Delete previous data
        PrequalificationSection::where('RoundId', $roundId)->delete();
        PrequalificationCriteria::where('RoundId', $roundId)->delete();

        // Re-insert updated data
        foreach ($sectionIds as $sectionId) {
            $weight = isset($weights[$sectionId]) ? floatval($weights[$sectionId]) : 0;

            $section = PrequalificationSection::create([
                'RoundId' => $roundId,
                'SectionId' => $sectionId,
                'Weight' => $weight,
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ]);

            $criteriaItems = Criteria::where('SectionId', $sectionId)->get();

            foreach ($criteriaItems as $criteria) {
                PrequalificationCriteria::create([
                    'RoundId' => $roundId,
                    'SectionId' => $sectionId,
                    'CriteriaId' => $criteria->id,
                    'Included' => true,
                    'CreatedBy' => auth()->id(),
                    'ModifiedBy' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('preqcriteria.index')->with('success', 'Evaluation structure updated successfully.');
    }


    public function destroy($id)
    {
        $section = PrequalificationSection::findOrFail($id);

        // Get the RoundId from the selected record
        $roundId = $section->RoundId;

        // Delete all sections with the same RoundId
        PrequalificationSection::where('RoundId', $roundId)->delete();

        // Also delete related criteria (optional but usually expected)
        PrequalificationCriteria::where('RoundId', $roundId)->delete();

        return redirect()->route('preqcriteria.index')->with('success', 'All sections and criteria for the round have been deleted successfully.');
    }


}
