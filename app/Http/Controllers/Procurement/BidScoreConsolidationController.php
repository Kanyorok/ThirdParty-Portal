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
    /**
     * Display consolidated scores for a specific tender
     */
    public function index(Request $request)
    {
        $tenderId = $request->get('tender_id');
        
        if (!$tenderId) {
            // If no tender specified, show tender selection or default view
            $tenders = Tender::select('Id', 'Title', 'TenderNo')->get();
            return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.select', compact('tenders'));
        }
        
        $consolidatedData = $this->getConsolidatedScores($tenderId);
        
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.index', $consolidatedData);
    }

    /**
     * Calculate consolidated scores for all bidders in a tender
     */
    protected function getConsolidatedScores($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);
        
        // Get responsive bids from BidSubmissions table (including evaluated ones)
        $bidders = \App\Models\Procurement\BidSubmission::where('TenderRef', $tender->TenderNo)
            ->where('IsResponsive', true)
            ->whereIn('BidStatus', ['responsive', 'evaluated']) // Include both responsive and evaluated bids
            ->select('Id', 'SupplierId', 'SupplierName', 'BidAmount', 'Currency')
            ->get()
            ->map(function ($bid) {
                return [
                    'id' => $bid->SupplierId,
                    'bid_id' => $bid->Id,
                    'name' => $bid->SupplierName ?? 'Unknown Supplier',
                    'bid_amount' => $bid->BidAmount,
                    'currency' => $bid->Currency
                ];
            });
        
        // Get tender sections with their weights
        $sections = $this->getTenderSections($tenderId);
        
        // Get all evaluations for this tender
        $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->with(['tenderCommitteeMember' => function($query) {
                $query->select('id', 'UserID', 'Role');
            }])
            ->select('MemberID', 'SectionID', 'CriteriaID', 'Score', 'MaxScore')
            ->get()
            ->groupBy(['SectionID', 'CriteriaID']);
        
        // Calculate consolidated scores for each bidder
        $consolidatedScores = [];
        foreach ($bidders as $bidder) {
            $consolidatedScores[$bidder['id']] = $this->calculateBidderScore($bidder, $sections, $evaluations, $tenderId);
        }
        
        // Sort bidders by total score (highest first)
        uasort($consolidatedScores, function ($a, $b) {
            return $b['total_weighted_score'] <=> $a['total_weighted_score'];
        });
        
        // Add rankings
        $rank = 1;
        foreach ($consolidatedScores as &$bidderScore) {
            $bidderScore['rank'] = $rank++;
            $bidderScore['recommendation'] = $this->getRecommendation($bidderScore['rank'], $bidderScore['total_weighted_score']);
        }
        
        return [
            'tender' => $tender,
            'sections' => $sections,
            'bidders' => $bidders,
            'consolidatedScores' => collect($consolidatedScores),
            'evaluatorCount' => $this->getEvaluatorCount($tenderId),
            'evaluations' => $evaluations // Pass raw evaluations for drill-down
        ];
    }

    /**
     * Get tender sections with their criteria and weights
     */
    protected function getTenderSections($tenderId)
    {
        return TenderSection::where('TenderID', $tenderId)
            ->with(['sections.criteria'])
            ->get()
            ->map(function ($tenderSection) {
                $section = $tenderSection->sections;
                return [
                    'id' => $section->Id,
                    'name' => $section->SectionName,
                    'weight' => $tenderSection->Weight ?? 100, // Use weight from TenderSection pivot
                    'criteria' => $section->criteria->map(function($criteria) {
                        return [
                            'id' => $criteria->Id,
                            'name' => $criteria->CriteriaName,
                            'max_score' => 10 // Standard max score per criteria
                        ];
                    })
                ];
            });
    }

    /**
     * Get criteria for a section
     */
    protected function getSectionCriteria($sectionId)
    {
        return Criteria::where('SectionID', $sectionId)
            ->select('id', 'Name', 'Weight', 'MaxScore')
            ->get()
            ->map(function ($criteria) {
                return [
                    'id' => $criteria->id,
                    'name' => $criteria->Name,
                    'weight' => $criteria->Weight ?? 10,
                    'max_score' => $criteria->MaxScore ?? 10
                ];
            });
    }

    /**
     * Calculate consolidated score for a specific bidder
     */
    protected function calculateBidderScore($bidder, $sections, $evaluations, $tenderId)
    {
        $sectionScores = [];
        $totalWeightedScore = 0;
        $totalSectionWeight = 0;

        foreach ($sections as $section) {
            $sectionScore = $this->calculateSectionScore($section, $evaluations, $tenderId);
            $sectionWeightedScore = ($sectionScore / 100) * $section['weight'];
            
            $sectionScores[] = [
                'section_id' => $section['id'],
                'section_name' => $section['name'],
                'score' => $sectionScore,
                'weight' => $section['weight'],
                'weighted_score' => $sectionWeightedScore
            ];
            
            $totalWeightedScore += $sectionWeightedScore;
            $totalSectionWeight += $section['weight'];
        }

        // Normalize to percentage if total weights don't equal 100
        if ($totalSectionWeight != 100 && $totalSectionWeight > 0) {
            $totalWeightedScore = ($totalWeightedScore / $totalSectionWeight) * 100;
        }

        return [
            'bidder_id' => $bidder['id'],
            'bidder_name' => $bidder['name'],
            'section_scores' => $sectionScores,
            'total_weighted_score' => round($totalWeightedScore, 2)
        ];
    }

    /**
     * Calculate average score for a section across all evaluators
     */
    protected function calculateSectionScore($section, $evaluations, $tenderId)
    {
        $totalCriteriaScore = 0;
        $criteriaCount = 0;

        foreach ($section['criteria'] as $criteria) {
            $criteriaEvaluations = $evaluations[$section['id']][$criteria['id']] ?? collect();
            
            if ($criteriaEvaluations->isNotEmpty()) {
                // Calculate average score across all evaluators for this criteria
                $averageScore = $criteriaEvaluations->avg('Score');
                $totalCriteriaScore += $averageScore;
                $criteriaCount++;
            }
        }

        // Return section average score as percentage
        if ($criteriaCount > 0) {
            $sectionAverage = $totalCriteriaScore / $criteriaCount;
            return ($sectionAverage / 10) * 100; // Convert to percentage (0-10 scale to 0-100)
        }
        
        return 0;
    }

    /**
     * Get number of evaluators for this tender
     */
    protected function getEvaluatorCount($tenderId)
    {
        return DB::table('t_TenderCommitteeMembers')
            ->where('TenderID', $tenderId)
            ->where('HasEvaluated', true)
            ->count();
    }

    /**
     * Get recommendation based on rank and score
     */
    protected function getRecommendation($rank, $score)
    {
        if ($rank === 1 && $score >= 70) {
            return ['status' => 'Recommended', 'class' => 'bg-success'];
        } elseif ($rank === 2 && $score >= 60) {
            return ['status' => 'Backup', 'class' => 'bg-secondary'];
        } else {
            return ['status' => 'Not Recommended', 'class' => 'bg-danger'];
        }
    }

    /**
     * Show section-wise drill-down for a specific tender
     */
    public function sectionDrilldown($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);
        $sections = $this->getTenderSections($tenderId);
        
        // Get all evaluations grouped by section and evaluator
        $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->with(['tenderCommitteeMember'])
            ->get()
            ->groupBy(['SectionID', 'MemberID']);
        
        $sectionDetails = [];
        foreach ($sections as $section) {
            $sectionEvaluations = $evaluations[$section['id']] ?? collect();
            
            $evaluatorScores = [];
            foreach ($sectionEvaluations as $memberId => $memberEvaluations) {
                $member = $memberEvaluations->first()->tenderCommitteeMember ?? null;
                $criteriaScores = [];
                $totalScore = 0;
                $criteriaCount = 0;
                
                foreach ($memberEvaluations as $evaluation) {
                    $criteriaScores[] = [
                        'criteria_id' => $evaluation->CriteriaID,
                        'score' => $evaluation->Score
                    ];
                    $totalScore += $evaluation->Score;
                    $criteriaCount++;
                }
                
                $averageScore = $criteriaCount > 0 ? $totalScore / $criteriaCount : 0;
                
                $evaluatorScores[] = [
                    'member_id' => $memberId,
                    'evaluator_name' => $member ? "User #{$member->UserID}" : 'Unknown',
                    'role' => $member ? $member->Role : 'Unknown',
                    'criteria_scores' => $criteriaScores,
                    'average_score' => round($averageScore, 2)
                ];
            }
            
            $sectionDetails[] = [
                'section' => $section,
                'evaluator_scores' => $evaluatorScores
            ];
        }
        
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.section-drilldown', [
            'tender' => $tender,
            'sectionDetails' => $sectionDetails
        ]);
    }
    
    /**
     * Show evaluator-wise drill-down for a specific tender
     */
    public function evaluatorDrilldown($tenderId)
    {
        $tender = Tender::findOrFail($tenderId);
        $sections = $this->getTenderSections($tenderId);
        
        // Get all evaluations grouped by evaluator and section
        $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->with(['tenderCommitteeMember'])
            ->get()
            ->groupBy(['MemberID', 'SectionID']);
        
        $evaluatorDetails = [];
        foreach ($evaluations as $memberId => $memberEvaluations) {
            $member = $memberEvaluations->flatten()->first()->tenderCommitteeMember ?? null;
            
            $sectionScores = [];
            $totalWeightedScore = 0;
            $totalWeight = 0;
            
            foreach ($sections as $section) {
                $sectionEvaluations = $memberEvaluations[$section['id']] ?? collect();
                
                if ($sectionEvaluations->isNotEmpty()) {
                    $sectionScore = $sectionEvaluations->avg('Score');
                    $sectionPercentage = ($sectionScore / 10) * 100;
                    $weightedScore = ($sectionPercentage * $section['weight']) / 100;
                    
                    $sectionScores[] = [
                        'section_id' => $section['id'],
                        'section_name' => $section['name'],
                        'section_weight' => $section['weight'],
                        'raw_score' => round($sectionScore, 2),
                        'percentage_score' => round($sectionPercentage, 2),
                        'weighted_score' => round($weightedScore, 2),
                        'criteria_count' => $sectionEvaluations->count()
                    ];
                    
                    $totalWeightedScore += $weightedScore;
                    $totalWeight += $section['weight'];
                }
            }
            
            $evaluatorDetails[] = [
                'member_id' => $memberId,
                'evaluator_name' => $member ? "User #{$member->UserID}" : 'Unknown',
                'role' => $member ? $member->Role : 'Unknown',
                'section_scores' => $sectionScores,
                'total_weighted_score' => round($totalWeightedScore, 2)
            ];
        }
        
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.evaluator-drilldown', [
            'tender' => $tender,
            'evaluatorDetails' => $evaluatorDetails
        ]);
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.create');
    }
}

