<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationRoundRequest;
use App\Http\Requests\Procurement\Suppliers\Prequalification\UpdatePrequalificationRoundRequest;
use App\Models\Procurement\Prequalification\PrequalificationCriteria;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\PrequalificationSection;
use App\Models\Procurement\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrequalificationRoundController extends Controller
{
    public function __construct()
    {
        // Removed authorizeResource() - it runs too early, before LoginBranchId is set in session
        // Using individual authorize() calls in each method instead (like RequisitionsController)
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PrequalificationRound::class);
        // Allow optionally including soft-deleted (archived) rounds via ?include_deleted=1
        $includeDeleted = (bool) $request->query('include_deleted', false);

        $query = PrequalificationRound::withCount('applications')->latest();
        if ($includeDeleted) {
            $query = $query->withTrashed();
        }

        $prequalificationRounds = $query->paginate(10);

        return view('procurement.suppliers.prequalification.prequalification-rounds.index', compact('prequalificationRounds', 'includeDeleted'));
    }

    public function create(): View
    {
        $this->authorize('create', PrequalificationRound::class);

        $masterSections = Section::with('criteria')->isActive()->get();
        $prequalificationRound = new PrequalificationRound();

        return view(
            'procurement.suppliers.prequalification.prequalification-rounds.create',
            compact('masterSections', 'prequalificationRound')
        );
    }

    public function store(StorePrequalificationRoundRequest $request): RedirectResponse
    {
        $this->authorize('create', PrequalificationRound::class);

        return $this->processRound($request);
    }

    public function show(PrequalificationRound $prequalificationRound): View
    {
        $this->authorize('view', $prequalificationRound);

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
        $this->authorize('update', $prequalificationRound);

        $masterSections = Section::with('criteria')->get();
        $prequalificationRound->load(['prequalificationSections', 'prequalificationCriteria']);

        return view('procurement.suppliers.prequalification.prequalification-rounds.edit', [
            'prequalificationRound' => $prequalificationRound,
            'masterSections' => $masterSections,
        ]);
    }

    public function update(UpdatePrequalificationRoundRequest $request, PrequalificationRound $prequalificationRound): RedirectResponse
    {
        $this->authorize('update', $prequalificationRound);

        return $this->processRound($request, $prequalificationRound);
    }

    public function destroy(PrequalificationRound $prequalificationRound): RedirectResponse
    {
        $this->authorize('delete', $prequalificationRound);

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
        $saveAsDraft = filter_var($request->input('save_as_draft'), FILTER_VALIDATE_BOOLEAN) || $request->input('save_as_draft') === '1';

        // Prevent any modifications to expired rounds
        if (! $isCreating) {
            $now = now();
            if ($prequalificationRound->EndDate && $prequalificationRound->EndDate < $now) {
                return redirect()->route('prequalification.prequalification-rounds.index')
                    ->with('error', 'This round has expired and can no longer be modified.');
            }
        }

        if ($isCreating) {
            $incomingStatus = $request->input('Status');
            $validated['Status'] = $saveAsDraft
                ? \App\Enums\Procurement\PrequalificationRoundEnum::Draft
                : ($incomingStatus ?? \App\Enums\Procurement\PrequalificationRoundEnum::Draft);
            $validated['CreatedBy'] = Auth::id();
            $validated['CreatedOn'] = now();
        } else {
            $incomingStatus = $request->input('Status', $prequalificationRound->Status->value);
            $validated['Status'] = $saveAsDraft ? \App\Enums\Procurement\PrequalificationRoundEnum::Draft : $incomingStatus;
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

            $message = $saveAsDraft
                ? ($isCreating ? 'Draft round saved.' : 'Draft changes saved.')
                : ($isCreating ? 'Prequalification round created successfully.' : 'Prequalification round updated successfully.');

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
            return ! empty($section['included']) && ! empty($section['section_id']);
        });

        // Prepare criteria data from included sections
        $includedCriteriaData = $includedSectionsData->flatMap(function ($section) use ($round, $userId, $now) {
            if (! isset($section['criteria']) || ! is_array($section['criteria'])) {
                return collect([]);
            }

            return collect($section['criteria'])
                ->filter(function ($criteria) {
                    return ! empty($criteria['included']) && ! empty($criteria['criteria_id']);
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
            if (! empty($sectionUpsertData)) {
                PrequalificationSection::upsert(
                    $sectionUpsertData,
                    ['RoundId', 'SectionId'],
                    ['Weight', 'ModifiedBy', 'ModifiedOn']
                );
            }

            // Upsert criteria
            if (! empty($includedCriteriaData)) {
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
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
