<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\PrequalificationSection;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Section;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationRoundRequest;
use App\Http\Requests\Procurement\Suppliers\Prequalification\UpdatePrequalificationRoundRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
        $prequalificationRound = new PrequalificationRound();
        return view(
            'procurement.suppliers.prequalification.prequalification-rounds.create',
            compact('masterSections', 'prequalificationRound')
        );
    }

    public function store(StorePrequalificationRoundRequest $request): RedirectResponse
    {
        return $this->processRound($request);
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
        return $this->processRound($request, $prequalificationRound);
    }

    public function destroy(PrequalificationRound $prequalificationRound): RedirectResponse
    {
        DB::transaction(function () use ($prequalificationRound) {
            $prequalificationRound->prequalificationCriteria()->delete();
            $prequalificationRound->prequalificationSections()->delete();
            $prequalificationRound->delete();
        });

        return redirect()->route('prequalification.prequalification-rounds.index')->with('success', 'Prequalification round deleted successfully.');
    }

    private function processRound($request, ?PrequalificationRound $prequalificationRound = null): RedirectResponse
    {
        $validated = $request->validated();
        $isCreating = is_null($prequalificationRound);

        if ($isCreating) {
            $validated['Status'] = $request->input('Status', \App\Enums\Procurement\PrequalificationRoundEnum::Draft);
            $validated['CreatedBy'] = Auth::id();
            $validated['CreatedOn'] = now();
        } else {
            $validated['Status'] = $request->input('Status', $prequalificationRound->Status->value);
            $validated['ModifiedBy'] = Auth::id();
            $validated['ModifiedOn'] = now();
        }

        DB::beginTransaction();
        try {
            if ($isCreating) {
                $round = PrequalificationRound::create($validated);
            } else {
                $prequalificationRound->update($validated);
                $round = $prequalificationRound;
            }

            $this->syncSectionsAndCriteria($round, $request->input('sections', []));

            DB::commit();

            $message = $isCreating ? 'Prequalification round created successfully.' : 'Prequalification round updated successfully.';
            return redirect()->route('prequalification.prequalification-rounds.index')->with('success', $message);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to process prequalification round: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Failed to process the prequalification round. Please try again.');
        }
    }

    private function syncSectionsAndCriteria(PrequalificationRound $round, array $sections): void
    {
    $now = now();
    $userId = Auth::id();

        // Filter only included sections
        $includedSectionsData = collect($sections)->filter(function ($section) {
            return !empty($section['included']) && !empty($section['section_id']);
        });

        // Prepare criteria data from included sections
        $includedCriteriaData = $includedSectionsData->flatMap(function ($section) use ($round, $userId, $now) {
            if (!isset($section['criteria']) || !is_array($section['criteria'])) {
                return collect([]);
            }

            return collect($section['criteria'])
                ->filter(function ($criteria) {
                    return !empty($criteria['included']) && !empty($criteria['criteria_id']);
                })
                ->map(function ($criteria) use ($round, $section, $userId, $now) {
                    return [
                        'RoundId' => $round->RoundID,
                        'SectionId' => $section['section_id'],
                        'CriteriaId' => $criteria['criteria_id'],
                        // Fixed per-criterion max score: always 10
                        'Weight' => 10,
                        'Included' => (int) ($criteria['included'] ?? 0),
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => $now,
                        'CreatedBy' => $userId,
                        'CreatedOn' => $now,
                    ];
                });
        })->values()->toArray();

        // Get IDs to keep
        $sectionsToKeep = $includedSectionsData->pluck('section_id')->filter()->values();
        $criteriaToKeep = collect($includedCriteriaData)->pluck('CriteriaId')->filter()->values();

        // Prepare section upsert data
        $sectionUpsertData = $includedSectionsData->map(function ($section) use ($round, $userId, $now) {
            return [
                'RoundId' => $round->RoundID,
                'SectionId' => $section['section_id'],
                'Weight' => (int) ($section['weight'] ?? 0),
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
            ];
        })->toArray();

        try {
            // Upsert sections
            if (!empty($sectionUpsertData)) {
                PrequalificationSection::upsert(
                    $sectionUpsertData,
                    ['RoundId', 'SectionId'],
                    ['Weight', 'ModifiedBy', 'ModifiedOn']
                );
            }

            // Upsert criteria
            if (!empty($includedCriteriaData)) {
                PrequalificationCriteria::upsert(
                    $includedCriteriaData,
                    ['RoundId', 'SectionId', 'CriteriaId'],
                    ['Weight', 'Included', 'ModifiedBy', 'ModifiedOn']
                );
            }

            // Delete sections not in the keep list
            if ($sectionsToKeep->isNotEmpty()) {
                $round->prequalificationSections()
                    ->whereNotIn('SectionId', $sectionsToKeep)
                    ->delete();
            } else {
                // If no sections to keep, delete all
                $round->prequalificationSections()->delete();
            }

            // Delete criteria not in the keep list
            if ($criteriaToKeep->isNotEmpty()) {
                $round->prequalificationCriteria()
                    ->whereNotIn('CriteriaId', $criteriaToKeep)
                    ->delete();
            } else {
                // If no criteria to keep, delete all
                $round->prequalificationCriteria()->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error syncing sections and criteria: ' . $e->getMessage(), [
                'round_id' => $round->RoundID,
                'sections_data' => $sections,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
