<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCommitteeEvaluation;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\TenderSection;
use App\Models\procurement\Section;
use App\Models\procurement\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BidScoreConsolidationController extends Controller
{
    public function index(Request $request)
    {
        $tenderId = $request->get('tender_id');
        
        if (!$tenderId) {
            // Show tender selection if no tender specified
            $tenders = Tender::with('tenderSuppliers')->where('Status', 'pb')->get();
            return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.select', compact('tenders'));
        }

        $tender = Tender::with(['tenderSuppliers.supplier', 'currency'])->findOrFail($tenderId);
        
        // Get all suppliers for this tender
        $suppliers = $tender->tenderSuppliers()->with('supplier')->get();
        
        // Get evaluation data
        $evaluationData = $this->getConsolidatedScores($tenderId);
        
        // Get sections and criteria for header display
        $sections = $this->getTenderSections($tenderId);
        
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.index', compact(
            'tender',
            'suppliers', 
            'evaluationData',
            'sections'
        ));
    }

    public function show($tenderId, $supplierId)
    {
        // Show detailed drill-down for specific supplier
        $tender = Tender::findOrFail($tenderId);
        $supplier = TenderSupplier::with('supplier')->where('TenderID', $tenderId)->where('SupplierID', $supplierId)->firstOrFail();
        
        // Get all evaluations for this supplier
        $evaluations = TenderCommitteeEvaluation::with([
            'tenderCommitteeMember.user', 
            'criteria',
            'section'
        ])
        ->where('TenderID', $tenderId)
        ->whereHas('tenderCommitteeMember', function($q) use ($supplierId) {
            $q->where('TenderID', $tenderId);
        })
        ->get()
        ->groupBy('MemberID');

        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.drilldown', compact(
            'tender',
            'supplier', 
            'evaluations'
        ));
    }

    private function getConsolidatedScores($tenderId)
    {
        // Get all suppliers for this tender
        $suppliers = TenderSupplier::with('supplier')->where('TenderID', $tenderId)->get();
        
        $consolidatedData = [];
        
        foreach ($suppliers as $supplier) {
            // Get all evaluations for this supplier across all committee members
            $evaluations = DB::table('t_TenderCommitteeEvaluations as tce')
                ->join('t_TenderCommitteeMembers as tcm', 'tce.MemberID', '=', 'tcm.Id')
                ->join('t_Sections as s', 'tce.SectionID', '=', 's.Id')
                ->join('t_Criterias as c', 'tce.CriteriaID', '=', 'c.Id')
                ->join('t_TenderSections as ts', function($join) use ($tenderId) {
                    $join->on('s.Id', '=', 'ts.SectionID')
                         ->where('ts.TenderID', '=', $tenderId);
                })
                ->where('tce.TenderID', $tenderId)
                ->where('tcm.TenderID', $tenderId)
                ->select(
                    'tce.MemberID',
                    'tce.SectionID', 
                    's.SectionName',
                    'ts.Weight as SectionWeight',
                    'tce.CriteriaID',
                    'c.CriteriaName',
                    'tce.MaxScore'
                )
                ->get();

            // Calculate section-wise scores
            $sectionScores = [];
            $totalWeightedScore = 0;
            
            // Group by section
            $evaluationsBySection = $evaluations->groupBy('SectionID');
            
            foreach ($evaluationsBySection as $sectionId => $sectionEvaluations) {
                $sectionName = $sectionEvaluations->first()->SectionName;
                $sectionWeight = $sectionEvaluations->first()->SectionWeight;
                
                // Calculate average score for this section across all evaluators
                $criteriaScores = [];
                $criteriaGroups = $sectionEvaluations->groupBy('CriteriaID');
                
                foreach ($criteriaGroups as $criteriaId => $criteriaEvaluations) {
                    $avgScore = $criteriaEvaluations->avg('MaxScore');
                    $criteriaScores[] = $avgScore;
                }
                
                // Section average (out of 10)
                $sectionAverage = count($criteriaScores) > 0 ? array_sum($criteriaScores) / count($criteriaScores) : 0;
                
                // Convert to percentage and apply section weight
                $sectionPercentage = ($sectionAverage / 10) * 100; // Convert to percentage
                $weightedSectionScore = ($sectionPercentage * $sectionWeight) / 100;
                
                $sectionScores[$sectionId] = [
                    'name' => $sectionName,
                    'weight' => $sectionWeight,
                    'raw_score' => $sectionAverage,
                    'percentage' => $sectionPercentage,
                    'weighted_score' => $weightedSectionScore
                ];
                
                $totalWeightedScore += $weightedSectionScore;
            }
            
            // Calculate rank (will be done after all suppliers)
            $consolidatedData[$supplier->SupplierID] = [
                'supplier' => $supplier->supplier,
                'section_scores' => $sectionScores,
                'total_weighted_score' => $totalWeightedScore,
                'rank' => 0 // Will be calculated later
            ];
        }
        
        // Sort by total weighted score and assign ranks
        uasort($consolidatedData, function($a, $b) {
            return $b['total_weighted_score'] <=> $a['total_weighted_score'];
        });
        
        $rank = 1;
        foreach ($consolidatedData as $supplierId => &$data) {
            $data['rank'] = $rank++;
            
            // Determine recommendation
            if ($data['rank'] == 1) {
                $data['recommendation'] = 'Recommended for Award';
            } elseif ($data['rank'] <= 3) {
                $data['recommendation'] = 'Reserve List';
            } else {
                $data['recommendation'] = 'Not Recommended';
            }
        }
        
        return $consolidatedData;
    }
    
    private function getTenderSections($tenderId)
    {
        return TenderSection::with('sections')
            ->where('TenderID', $tenderId)
            ->get();
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.create');
    }
}
