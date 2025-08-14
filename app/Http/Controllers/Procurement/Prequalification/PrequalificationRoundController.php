<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\PrequalificationSection;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Section;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationRoundRequest;
use App\Http\Requests\Procurement\Suppliers\Prequalification\UpdatePrequalificationRoundRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrequalificationRoundController extends Controller
{
    public function index(): View
    {
        $prequalificationRounds = PrequalificationRound::withCount('applications')->latest()->paginate(10);
        return view('procurement.suppliers.prequalification.prequalification-rounds.index', compact('prequalificationRounds'));
    }

    public function create(): View
    {
        $masterSections = Section::with('criteria')->get();
        return view('procurement.suppliers.prequalification.prequalification-rounds.create', ['masterSections' => $masterSections]);
    }

    public function store(StorePrequalificationRoundRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['Status'] = PrequalificationRoundEnum::Draft;
        $validated['CreatedBy'] = auth()->id();

        DB::beginTransaction();
        try {
            $round = PrequalificationRound::create($validated);
            $this->syncSectionsAndCriteria($round, $request->input('sections', []));
            DB::commit();
            return redirect()->route('prequalification.prequalification-rounds.index')->with('success', 'Prequalification round created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to create prequalification round: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Failed to create the prequalification round. Please try again.');
        }
    }

    public function show(PrequalificationRound $prequalificationRound): View
    {
        $prequalificationRound->load([
            'prequalificationSections.masterSection',
            'prequalificationCriteria.masterCriteria',
            'applications.supplier',
        ]);

        $masterSections = Section::with('criteria')->get();
        return view('procurement.suppliers.prequalification.prequalification-rounds.show', compact('prequalificationRound', 'masterSections'));
    }

    public function edit(PrequalificationRound $prequalificationRound): View
    {
        $masterSections = Section::with('criteria')->get();
        $prequalificationRound->load(['prequalificationSections', 'prequalificationCriteria']);
        return view('procurement.suppliers.prequalification.prequalification-rounds.edit', [
            'prequalificationRound' => $prequalificationRound,
            'masterSections' => $masterSections
        ]);
    }

    public function update(UpdatePrequalificationRoundRequest $request, PrequalificationRound $prequalificationRound): RedirectResponse
    {
        $validated = $request->validated();
        $validated['ModifiedBy'] = auth()->id();

        DB::beginTransaction();
        try {
            $prequalificationRound->update($validated);
            $prequalificationRound->prequalificationSections()->delete();
            $prequalificationRound->prequalificationCriteria()->delete();
            $this->syncSectionsAndCriteria($prequalificationRound, $request->input('sections', []));
            DB::commit();
            return redirect()->route('prequalification.prequalification-rounds.index')->with('success', 'Prequalification round updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to update prequalification round: {$e->getMessage()}");
            return back()->withInput()->with('error', 'Failed to update the prequalification round. Please try again.');
        }
    }

    public function destroy(PrequalificationRound $prequalificationRound): RedirectResponse
    {
        $prequalificationRound->delete();
        return redirect()->route('prequalification.prequalification-rounds.index')->with('success', 'Prequalification round deleted successfully.');
    }

    private function syncSectionsAndCriteria(PrequalificationRound $round, array $sections): void
    {
        $sectionsToSave = collect($sections)
            ->map(function ($sectionData) use ($round) {
                if (empty($sectionData['included'])) return null;

                $sectionModel = new PrequalificationSection([
                    'RoundId' => $round->RoundID,
                    'SectionId' => $sectionData['section_id'],
                    'Weight' => $sectionData['weight'] ?? 0,
                    'CreatedBy' => auth()->id(),
                ]);

                if (!empty($sectionData['criteria_ids'])) {
                    $criteriaModels = collect($sectionData['criteria_ids'])->map(
                        fn($criteriaId) => new PrequalificationCriteria([
                            'RoundId' => $round->RoundID,
                            'SectionId' => $sectionData['section_id'],
                            'CriteriaId' => $criteriaId,
                            'Included' => true,
                            'CreatedBy' => auth()->id(),
                        ])
                    );
                    $sectionModel->setRelation('criteria', $criteriaModels);
                }

                return $sectionModel;
            })
            ->filter()
            ->values();

        $round->prequalificationSections()->saveMany($sectionsToSave);

        foreach ($sectionsToSave as $section) {
            if ($section->relationLoaded('criteria')) {
                $round->prequalificationCriteria()->saveMany($section->criteria);
            }
        }
    }
}
