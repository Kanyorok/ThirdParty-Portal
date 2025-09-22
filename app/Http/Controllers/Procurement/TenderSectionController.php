<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderSection;
use App\Models\Procurement\Section;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderSectionController extends Controller
{
    /**
     * Display the tender sections setup page
     */
    public function index()
    {
        $this->authorize(PermissionEnum::TenderRead);
        
        $tenders = Tender::with(['tenderSections.sections'])
            ->where('Status', '!=', 'Draft')
            ->select('Id', 'TenderNo', 'Title', 'Status')
            ->orderBy('CreatedOn', 'desc')
            ->get();

        $sections = Section::isActive()->with('criteria')->get();

        return view('procurement.tendering.settings.tender-sections', 
            compact('tenders', 'sections'));
    }

    /**
     * Show the form for assigning sections to a specific tender
     */
    public function show(Request $request)
    {
        $this->authorize(PermissionEnum::TenderRead);
        
        $tenderId = $request->get('tender');
        if (!$tenderId) {
            return redirect()->back()->with('error', 'Please select a tender to configure sections.');
        }

        $tender = Tender::with(['tenderSections.sections.criteria'])
            ->findOrFail($tenderId);

        $availableSections = Section::isActive()->with('criteria')->get();
        
        // Get currently assigned sections with their weights
        $assignedSections = $tender->tenderSections->pluck('sections', 'SectionID')->flatten();
        $sectionWeights = $tender->tenderSections->pluck('Weight', 'SectionID');

        // Calculate total weight to show validation status
        $totalWeight = $sectionWeights->sum();

        return view('procurement.tendering.settings.tender-sections-assign', compact(
            'tender', 'availableSections', 'assignedSections', 'sectionWeights', 'totalWeight'
        ));
    }

    /**
     * Store or update section assignments for a tender
     */
    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::TenderWrite);
        
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'sections' => 'required|array|min:1',
            'sections.*' => 'exists:t_Sections,Id',
            'weights' => 'required|array',
            'weights.*' => 'numeric|min:0|max:100',
        ]);

        // Validate that weights sum to 100%
        $totalWeight = array_sum($request->weights);
        if (abs($totalWeight - 100) > 0.01) { // Allow small floating point differences
            return redirect()->back()->withErrors([
                'weights' => "Section weights must sum to exactly 100%. Current total: {$totalWeight}%"
            ])->withInput();
        }

        $tenderId = $request->tender_id;
        $sections = $request->sections;
        $weights = $request->weights;

        DB::beginTransaction();

        try {
            // Remove existing section assignments
            TenderSection::where('TenderID', $tenderId)->delete();

            // Create new section assignments
            foreach ($sections as $sectionId) {
                $weight = $weights[$sectionId] ?? 0;
                
                TenderSection::create([
                    'TenderID' => $tenderId,
                    'SectionID' => $sectionId,
                    'Weight' => $weight,
                    'IsActive' => true,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            }

            // Log the activity
            activity()
                ->performedOn(Tender::find($tenderId))
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'assign_evaluation_sections',
                    'sections_count' => count($sections),
                    'total_weight' => $totalWeight,
                    'sections' => $sections
                ])
                ->log("Evaluation sections assigned to tender ID: {$tenderId}");

            DB::commit();

            return redirect()->back()->with('success', 
                'Evaluation sections assigned successfully! Total weight: ' . $totalWeight . '%');

        } catch (\Exception $e) {
            DB::rollBack();
            
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'assign_evaluation_sections_failed',
                    'tender_id' => $tenderId,
                    'error' => $e->getMessage()
                ])
                ->log('Failed to assign evaluation sections to tender: ' . $e->getMessage());

            return redirect()->back()->with('error', 
                'Failed to assign sections: ' . $e->getMessage());
        }
    }

    /**
     * Get sections and criteria for a specific tender (AJAX)
     */
    public function getTenderSections($tenderId)
    {
        $this->authorize(PermissionEnum::TenderRead);
        
        $tender = Tender::with(['tenderSections.sections.criteria'])
            ->findOrFail($tenderId);

        $sectionsData = $tender->tenderSections->map(function ($tenderSection) {
            $section = $tenderSection->sections;
            return [
                'section_id' => $section->Id,
                'section_name' => $section->SectionName,
                'section_description' => $section->Description,
                'weight' => $tenderSection->Weight,
                'criteria' => $section->criteria->map(function ($criteria) {
                    return [
                        'criteria_id' => $criteria->Id,
                        'criteria_name' => $criteria->CriteriaName,
                        'criteria_description' => $criteria->Description,
                        'max_score' => 10, // Standard max score per criteria
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'tender' => [
                'id' => $tender->Id,
                'tender_no' => $tender->TenderNo,
                'title' => $tender->Title
            ],
            'sections' => $sectionsData,
            'total_weight' => $sectionsData->sum('weight')
        ]);
    }

    /**
     * Remove section assignments from a tender
     */
    public function destroy($tenderId)
    {
        $this->authorize(PermissionEnum::TenderWrite);
        
        DB::beginTransaction();

        try {
            $tender = Tender::findOrFail($tenderId);
            $sectionsCount = $tender->tenderSections()->count();
            
            $tender->tenderSections()->delete();

            activity()
                ->performedOn($tender)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'remove_evaluation_sections',
                    'sections_removed' => $sectionsCount
                ])
                ->log("Removed all evaluation sections from tender ID: {$tenderId}");

            DB::commit();

            return redirect()->back()->with('success', 
                'All evaluation sections removed from tender successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 
                'Failed to remove sections: ' . $e->getMessage());
        }
    }

    /**
     * Validate section weights for a tender
     */
    public static function validateSectionWeights($tenderId)
    {
        $totalWeight = TenderSection::where('TenderID', $tenderId)
            ->where('IsActive', true)
            ->sum('Weight');

        return [
            'is_valid' => abs($totalWeight - 100) < 0.01,
            'total_weight' => $totalWeight,
            'message' => abs($totalWeight - 100) < 0.01 
                ? 'Section weights are properly configured' 
                : "Section weights sum to {$totalWeight}%, should be 100%"
        ];
    }

    /**
     * Get evaluation readiness status for a tender
     */
    public static function getEvaluationReadiness($tenderId)
    {
        $tender = Tender::with('tenderSections')->find($tenderId);
        
        if (!$tender) {
            return ['ready' => false, 'message' => 'Tender not found'];
        }

        if ($tender->tenderSections->isEmpty()) {
            return ['ready' => false, 'message' => 'No evaluation sections assigned'];
        }

        $weightValidation = self::validateSectionWeights($tenderId);
        if (!$weightValidation['is_valid']) {
            return ['ready' => false, 'message' => $weightValidation['message']];
        }

        // Check if tender has responsive bids
        $responsiveBids = $tender->submissions()
            ->where('BidStatus', 'responsive')
            ->where('IsResponsive', true)
            ->count();

        if ($responsiveBids === 0) {
            return ['ready' => false, 'message' => 'No responsive bids available for evaluation'];
        }

        return [
            'ready' => true, 
            'message' => "Ready for evaluation: {$responsiveBids} responsive bid(s), " . 
                        $tender->tenderSections->count() . " section(s) configured",
            'responsive_bids' => $responsiveBids,
            'sections_count' => $tender->tenderSections->count()
        ];
    }
}
