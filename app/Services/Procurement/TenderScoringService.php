<?php

namespace App\Services\Procurement;

use Illuminate\Support\Facades\DB;

class TenderScoringService
{
    /**
     * Compute supplier scores for a tender using per-evaluator weighted totals, then averaging evaluators.
     * Returns an array keyed by supplierId with:
     *  - final: float total score out of 100
     *  - section_avgs: [sectionId => percent]
     *  - member_totals: [memberId => percent]
     *  - sections: [ [id,name,weight] ... ] (order preserved)
     */
    public static function compute(int $tenderId, bool $onlyAccepted = false): array
    {
        $sections = self::getSections($tenderId);

        // Optionally restrict to accepted, active committee members
        $accepted = [];
        if ($onlyAccepted) {
            $accepted = DB::table('t_TenderCommitteeMembers')
                ->where('TenderID', $tenderId)
                ->where('IsActive', 1)
                ->where('Response', 1)
                ->pluck('Id')
                ->map(fn ($v) => (int)$v)
                ->all();
        }

        // Group evaluations Supplier -> Member -> Section -> Criteria
        $evalsQuery = DB::table('t_TenderCommitteeEvaluations')
            ->select('SupplierId', 'MemberID', 'SectionID', 'CriteriaID', 'Score', 'MaxScore')
            ->where('TenderID', $tenderId)
            ->when($onlyAccepted && ! empty($accepted), function ($q) use ($accepted) {
                $q->whereIn('MemberID', $accepted);
            })
            ->get()
            ->groupBy(['SupplierId','MemberID','SectionID','CriteriaID']);
        $evals = $evalsQuery;

        // No per-criterion normalization here to keep parity with consolidation:
        // use the average of recorded criterion scores (out of 10) within a section

        $results = [];
        foreach ($evals as $supplierId => $memberGroups) {
            $memberTotals = [];
            $sectionSums = array_fill_keys(array_map(fn ($s) => $s['id'], $sections), 0.0);
            $memberCount = 0;

            foreach ($memberGroups as $memberId => $sectionGroups) {
                $memberCount++;
                $total = 0.0;
                foreach ($sections as $sec) {
                    $sid = (int)$sec['id'];
                    $rowsByCriteria = $sectionGroups[$sid] ?? collect();
                    // Flatten all criteria rows within this section for this evaluator
                    $flat = collect($rowsByCriteria)->flatten(1);
                    if ($flat->isEmpty()) {
                        $percent = 0.0;
                    } else {
                        $avgOutOf10 = (float)$flat->avg('Score');
                        $percent = ($avgOutOf10 / 10.0) * 100.0;
                    }
                    $sectionSums[$sid] += $percent;
                    $total += ($percent * (float)$sec['weight']) / 100.0;
                }
                $memberTotals[(int)$memberId] = round($total, 2);
            }

            $final = count($memberTotals) > 0 ? round(array_sum($memberTotals) / count($memberTotals), 2) : 0.0;
            $sectionAvgs = [];
            foreach ($sections as $sec) {
                $sid = (int)$sec['id'];
                $sectionAvgs[$sid] = $memberCount > 0 ? round($sectionSums[$sid] / $memberCount, 2) : 0.0;
            }

            $results[(int)$supplierId] = [
                'final' => $final,
                'section_avgs' => $sectionAvgs,
                'member_totals' => $memberTotals,
                'sections' => array_map(fn ($s) => ['id' => $s['id'],'name' => $s['name'],'weight' => $s['weight']], $sections),
            ];
        }

        return $results;
    }

    private static function getSections(int $tenderId): array
    {
        // Pull sections + tender criteria (MaxScore), exclude soft-deleted/disabled and dedupe
        $rows = DB::table('t_TenderSection as ts')
            ->join('t_Sections as s', 's.Id', '=', 'ts.SectionID')
            ->where('ts.TenderID', $tenderId)
            ->where('ts.IsActive', 1)
            ->whereNull('ts.DeletedOn')
            ->orderByDesc('ts.Id')
            ->select('ts.Id as TSID', 'ts.SectionID as Id', 's.SectionName as Name', 'ts.Weight')
            ->get()
            ->unique('Id')
            ->sortByDesc('Weight')
            ->values();

        $sections = [];
        foreach ($rows as $row) {
            $criteria = DB::table('t_TenderCriteria as tc')
                ->join('t_Criterias as c', 'c.Id', '=', 'tc.CriteriaID')
                ->where('tc.TenderID', $tenderId)
                ->where('tc.SectionID', $row->Id)
                ->where('tc.IsActive', 1)
                ->whereNull('tc.DeletedOn')
                ->select('tc.CriteriaID as Id', 'c.CriteriaName as Name', 'tc.MaxScore')
                ->get()
                ->map(fn ($r) => ['id' => (int)$r->Id,'name' => $r->Name,'max_score' => (float)($r->MaxScore ?? 10)])
                ->values()
                ->all();
            $sections[] = ['id' => (int)$row->Id,'name' => $row->Name,'weight' => (float)($row->Weight ?? 100),'criteria' => $criteria];
        }

        return $sections;
    }
}
