<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCriteria;
use App\Models\Procurement\TenderSection;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TenderEvaluationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        
        $sections = Section::select('Id', 'SectionName')->get();
        
        // Get unique tenderID from the TenderSection table
        $tenderSections = TenderSection::select('TenderID')->distinct()->get();
        
        // Get tender IDs that have sections
        $tenderIds = $tenderSections->pluck('TenderID')->toArray();
        
        // Get tenders that are not in the TenderSection table (with search and sort)
        $tendersQuery = Tender::whereNotIn('Id', $tenderIds);
        
        if (!empty($search)) {
            $tendersQuery->where(function($q) use ($search) {
                $q->where('TenderNo', 'like', '%' . $search . '%')
                  ->orWhere('Title', 'like', '%' . $search . '%');
            });
        }
        
        $tenders = $tendersQuery->orderBy('CreatedOn', 'desc')->get();
        
        // Get tenders that have sections (with search and sort)
        $tenderswithsectionsQuery = Tender::whereIn('Id', $tenderIds);
        
        if (!empty($search)) {
            $tenderswithsectionsQuery->where(function($q) use ($search) {
                $q->where('TenderNo', 'like', '%' . $search . '%')
                  ->orWhere('Title', 'like', '%' . $search . '%');
            });
        }
        
        $tenderswithsections = $tenderswithsectionsQuery->orderBy('CreatedOn', 'desc')->get();

        $data = [];
        foreach ($tenderswithsections as $key => $value) {
            // Collect valid section names linked to this tender
            $tenderSectionRows = TenderSection::where('TenderID', $value->Id)
                ->with('sections')
                ->get();
            $sectionNames = $tenderSectionRows
                ->map(fn($ts) => $ts->sections?->SectionName)
                ->filter()
                ->values()
                ->all();

            // Fetch selected criteria for this tender and group them by section for display
            $selectedTenderCriteria = TenderCriteria::where('TenderID', $value->Id)
                ->where('IsActive', true)
                // Ensure correct PK casing so relations hydrate properly
                ->with([
                    'criteria:Id,CriteriaName,SectionID',
                    'section:Id,SectionName'
                ])
                ->get();

            // Flat list of selected criteria names
            $criteriaNamesFlat = $selectedTenderCriteria
                ->map(fn($tc) => optional($tc->criteria)->CriteriaName)
                ->filter()
                ->unique()
                ->values()
                ->all();

            // Group selected criteria names by Section name
            $criteriaBySection = [];
            $selectedTenderCriteria
                ->groupBy('SectionID')
                ->each(function ($rows, $sectionId) use (&$criteriaBySection) {
                    $first = $rows->first();
                    $sectionName = optional($first->section)->SectionName ?: ('Section #'.$sectionId);
                    $names = $rows
                        ->map(fn($tc) => optional($tc->criteria)->CriteriaName)
                        ->filter()
                        ->values()
                        ->all();
                    $criteriaBySection[$sectionName] = $names;
                });

            $data[] = [
                'id' => $value->Id,
                'TenderNo' => $value->TenderNo,
                'Title' => $value->Title,
                // Show only sections that still have a valid base Section row
                'sectionsNumber' => count($sectionNames),
                'sectionNames' => $sectionNames,
                'criteriaNumber' => TenderCriteria::where('TenderID', $value->Id)
                    ->where('IsActive', true)
                    ->count(),
                // Provide selected criteria for modal display
                'criteriaNames' => $criteriaNamesFlat,
                'criteriaBySection' => $criteriaBySection,
            ];
        }
        //return$data;
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.tenderevaluations', compact(
            'tenders',
            'sections',
            'data'
        ));
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
        return $request;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

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

    //Store Sections associated to a tender
    public function tenderSections(Request $request)
    {
        //check if user has permission to create tender sections
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);

        // Log the incoming request data for debugging
        Log::info('Tender sections form submission', [
            'tender_id' => $request->tender_id,
            'sections' => $request->sections,
            'weights' => $request->weights,
            'all_data' => $request->all()
        ]);

        // Validate the request data
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'sections' => 'required|array|min:1',
            'sections.*' => 'exists:t_Sections,Id',
            'weights' => 'required|array',
        ]);

        $tenderId = $request->tender_id;
        $sections = $request->sections;
        $weights = $request->weights;

        // Calculate total weight for selected sections only
        $totalWeight = 0;
        foreach ($sections as $sectionId) {
            $totalWeight += floatval($weights[$sectionId] ?? 0);
        }

        // Check if the total weight is 100
        if (abs($totalWeight - 100) > 0.01) { // Allow small floating point differences
            return back()->with('error', 'The total weight must be exactly 100%. Current total: ' . $totalWeight . '%');
        }

        DB::beginTransaction();
        try {
            // First, delete existing sections for this tender to avoid duplicates
            TenderSection::where('TenderID', $tenderId)->delete();

            // Loop through each selected section and add with its weight
            foreach ($sections as $sectionId) {
                // Check if the section exists
                $section = Section::find($sectionId);
                if (!$section) {
                    DB::rollBack();
                    return back()->with('error', 'Section with ID ' . $sectionId . ' does not exist.');
                }

                // Create or update the tender section
                $tenderSection = TenderSection::create([
                    'TenderID' => $request->tender_id, // Assuming tender_id is passed in the request
                    'SectionID' => $sectionId,
                    'Weight' => (float)($weights[$sectionId] ?? 0),
                    'IsActive' => true, // Assuming sections are active by default
                    'Comments' => $request->comments[$sectionId] ?? null, // Optional comments
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);

                activity()
                    ->performedOn($tenderSection)
                    ->causedBy(Auth::id())
                    ->log('Created or updated tender sections for tender ID: ' . $tenderId);
            }

            DB::commit();

            // Log success
            Log::info('Tender sections created successfully', [
                'tender_id' => $tenderId,
                'sections_count' => count($sections),
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Tender sections created successfully.');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create tender sections', [
                'error' => $th->getMessage(),
                'tender_id' => $tenderId,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to create tender sections: ' . $th->getMessage());
        }
    }

    //Get Criteria associated to a tender section
    public function getTenderCriteria(Request $request, $TenderId)
    {
        $tender = Tender::findOrFail($TenderId);

        // Fetch active criteria grouped by SectionID for this tender
        $existingBySection = TenderCriteria::where('TenderID', $TenderId)
            ->where('IsActive', true)
            ->get(['CriteriaID', 'SectionID'])
            ->groupBy('SectionID')
            ->map(fn($rows) => $rows->pluck('CriteriaID')->toArray());

        // Get sections associated with the tender
        $allTenderSections = TenderSection::where('TenderID', $tender->Id)
            ->with('sections')
            ->get();

        // Include any tender section that has a valid Section row (even if IsActive is false)
        $tenderSections = $allTenderSections
            ->filter(function($ts){
                return (bool) $ts->sections; // has linked Section
            })
            ->values();

        // Only warn about sections truly missing their base Section definition
        $filteredSections = $allTenderSections
            ->reject(function($ts){
                return (bool) $ts->sections;
            })
            ->map(function($ts){
                $name = $ts->sections?->SectionName;
                return $name ?: ('Section #'.$ts->SectionID);
            })
            ->values();

        foreach ($tenderSections as $section) {
            $sectionId = $section->sections?->Id ?? $section->SectionID;
            $criteriaList = Criteria::where('SectionID', $sectionId)->get();
            $selectedForSection = ($existingBySection instanceof \Illuminate\Support\Collection)
                ? ($existingBySection->get($sectionId, []))
                : ($existingBySection[$sectionId] ?? []);
            foreach ($criteriaList as $criteria) {
                $criteria->isChecked = in_array($criteria->Id, $selectedForSection);
            }
            $section->criteria = $criteriaList;
        }

        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.tenderCriteria', compact(
            'TenderId',
            'tender',
            'tenderSections',
            'filteredSections'
        ));
    }


    public function storeTenderCriteria(Request $request)
    {
        $this->authorize(PermissionEnum::TenderWrite, Tender::class);
        $validated = $request->validate([
            'TenderId' => 'required|integer',
            'weights' => 'required|array',
            'criterias' => 'nullable|array',
        ]);

        $tenderId = $validated['TenderId'];
        $weights = $validated['weights'];
        $criterias = $validated['criterias'] ?? [];

        try {
            DB::beginTransaction();

            // For each section, update its weight and sync only the selected criteria
            foreach ($weights as $sectionId => $weight) {
                // Ensure TenderSection exists with updated weight
                TenderSection::updateOrCreate(
                    [
                        'TenderID' => (int)$tenderId,
                        'SectionID' => (int)$sectionId,
                    ],
                    [
                        'Weight' => (float)$weight,
                        'IsActive' => true,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                    ]
                );

                $selected = array_map('intval', $criterias[$sectionId] ?? []);

                // Remove any criteria not selected for this section
                $removeQuery = TenderCriteria::where('TenderID', (int)$tenderId)
                    ->where('SectionID', (int)$sectionId);
                if (!empty($selected)) {
                    $removeQuery->whereNotIn('CriteriaID', $selected);
                }
                // Soft-delete if model has SoftDeletes, else hard delete
                $removeQuery->delete();

                // Upsert selected criteria as active
                foreach ($selected as $criteriaId) {
                    TenderCriteria::updateOrCreate(
                        [
                            'TenderID' => (int)$tenderId,
                            'SectionID' => (int)$sectionId,
                            'CriteriaID' => (int)$criteriaId,
                        ],
                        [
                            'MaxScore' => 10,
                            'IsActive' => true,
                            'CreatedBy' => Auth::id(),
                            'CreatedOn' => now(),
                            'ModifiedBy' => Auth::id(),
                            'ModifiedOn' => now(),
                        ]
                    );
                }
            }

            DB::commit();
            // Log the action
            activity()
                ->performedOn(new Tender())
                ->causedBy(Auth::id())
                ->log('Saved tender criteria for tender ID: ' . $tenderId);

            return redirect()->route('tenderevaluations.index')->with('success', 'Tender criteria saved successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error saving tender criteria', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function criteriaScores(Request $request)
    {
        // ✅ Validate incoming request
        $validated = $request->validate([
            'TenderId' => 'required|integer|exists:t_Tenders,id',
            'selected_criteria' => 'required|array',
            'selected_criteria.*' => 'integer',
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:10',
            'section_ids' => 'required|array',
            'section_ids.*' => 'integer',
            // 'CommitteeID' => 'nullable|integer|exists:t_Committees,id' // uncomment if CommitteeID is passed
        ]);

        DB::beginTransaction();

        try {
            $tenderId = $validated['TenderId'];
            $memberId = Auth::id(); // logged in user
            $createdBy = Auth::id();

            $selectedCriteria = $validated['selected_criteria'];
            $scores = $validated['scores'];
            $sectionIds = $validated['section_ids'];

            foreach ($selectedCriteria as $criteriaId) {
                $score = $scores[$criteriaId] ?? 0;
                $sectionId = $sectionIds[$criteriaId] ?? null;
                $committeeId = DB::table('t_TenderCommitteeMembers')
                    ->where('TenderID', $request->TenderId)
                    ->where('UserID', Auth::id())
                    ->value('CommitteeID'); // Assuming `id` is the PK of the committee table
                if ($sectionId !== null) {
                    DB::table('t_TenderCommitteeEvaluations')->updateOrInsert(
                        [
                            'TenderID' => (int)$tenderId,
                            'MemberID' => (int)$memberId,
                            'SectionID' => (int)$sectionId,
                            'CommitteeID' => (int)$committeeId,
                            'CriteriaID' => (int)$criteriaId,
                        ],
                        [
                            'MaxScore' => (float)$score,
                            'CreatedBy' => (string)$createdBy,
                            'CreatedOn' => now(),
                            'ModifiedOn' => now(),
                            'ModifiedBy' => Auth::id(),
                        ]
                    );
                }
            }
            //Update HasEvaluated field in the tender
            DB::table('t_TenderCommitteeMembers')
                ->where('TenderID', $tenderId)
                ->where('UserID', $memberId)
                ->update(['HasEvaluated' => true, 'ModifiedBy' => $createdBy, 'ModifiedOn' => now()]);
            // Log the action
            activity()
                ->performedOn(new Tender())
                ->causedBy(Auth::id())
                ->log('Scores submitted for tender ID: ' . $tenderId);

            DB::commit();
            return redirect()->route('evaluationdashboard.index')->with('success', 'Scores submitted successfully!');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to save scores: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Something went wrong while saving. Please try again.']);
        }
    }

}
