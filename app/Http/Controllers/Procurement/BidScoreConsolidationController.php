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
        
        // Get only responsive bidders/suppliers for this tender
        $bidders = TenderSupplier::where('TenderID', $tenderId)
            ->with(['supplier.thirdParty', 'bidResponsiveness'])
            ->whereHas('bidResponsiveness', function ($query) {
                $query->where('IsResponsive', true);
            })
            ->get()
            ->filter(function ($item) {
                return $item->supplier !== null; // Filter out entries with null suppliers
            })
            ->map(function ($item) {
                return [
                    'id' => $item->supplier->Id,
                    'name' => $item->supplier->thirdParty->ThirdPartyName ?? 'Unknown Supplier',
                ];
            });
        
        // Get tender sections with their weights
        $sections = $this->getTenderSections($tenderId);
        
        // Get all evaluations for this tender
        $evaluations = TenderCommitteeEvaluation::where('TenderID', $tenderId)
            ->select('MemberID', 'SectionID', 'CriteriaID', 'MaxScore')
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
            'consolidatedScores' => collect($consolidatedScores), // Convert to collection for easier handling in view
            'evaluatorCount' => $this->getEvaluatorCount($tenderId)
        ];
    }

    /**
     * Get tender sections with their criteria and weights
     */
    protected function getTenderSections($tenderId)
    {
        return TenderSection::where('TenderID', $tenderId)
            ->with(['sections' => function ($query) {
                $query->select('id', 'Name', 'Weight');
            }])
            ->get()
            ->map(function ($tenderSection) {
                $section = $tenderSection->sections;
                return [
                    'id' => $section->id,
                    'name' => $section->Name,
                    'weight' => $section->Weight ?? 100, // Default weight if not set
                    'criteria' => $this->getSectionCriteria($section->id)
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
        $criteriaScores = [];
        $totalCriteriaWeight = 0;
        $weightedScoreSum = 0;

        foreach ($section['criteria'] as $criteria) {
            $criteriaEvaluations = $evaluations[$section['id']][$criteria['id']] ?? collect();
            
            if ($criteriaEvaluations->isNotEmpty()) {
                // Calculate average score across all evaluators
                $averageScore = $criteriaEvaluations->avg('MaxScore');
                $normalizedScore = ($averageScore / 10) * 100; // Convert to percentage
                
                $criteriaWeight = $criteria['weight'];
                $weightedScoreSum += $normalizedScore * ($criteriaWeight / 100);
                $totalCriteriaWeight += $criteriaWeight;
            }
        }

        // Return section percentage score
        return $totalCriteriaWeight > 0 ? round($weightedScoreSum, 2) : 0;
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

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.create');
    }
}
