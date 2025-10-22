<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCriteria;
use App\Models\Procurement\RFQSection;
use App\Models\Procurement\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RFQCriteriaController extends Controller
{
    public function show($rfqId)
    {
        $rfq = RFQ::findOrFail($rfqId);

        // Get active assigned RFQ sections with their criteria
        $rfqSections = RFQSection::with(['section.criteria'])
            ->where('RFQID', $rfqId)
            ->where('IsActive', true)
            ->get();

        // Get already assigned criteria
        $existingCriteria = RFQCriteria::where('RFQID', $rfqId)->get();

        // Mark checked criteria
        foreach ($rfqSections as $rfqSection) {
            foreach ($rfqSection->section->criteria as $criteria) {
                $criteria->isChecked = $existingCriteria->contains(function ($item) use ($criteria, $rfqSection) {
                    return (int)$item->CriteriaID === (int)$criteria->Id && (int)$item->SectionID === (int)$rfqSection->section->Id;
                });
            }
        }


        $hasExisting = $existingCriteria->count() > 0;
        return view('procurement.rfqcriteriasetup.rfqCriteria', [
            'rfq' => $rfq,
            'rfqSections' => $rfqSections,
            'isEdit' => $hasExisting,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required|exists:t_RFQ,Id',
            'weights' => 'required|array',
            'criterias' => 'nullable|array',
            'removed_sections' => 'nullable|array'
        ]);

        $rfqId = (int)$request->rfq_id;
        $weights = $request->input('weights', []);
        $criterias = $request->input('criterias', []); // sectionId => [criteriaIds]
        $removed = $request->input('removed_sections', []); // section IDs removed by user

        // Filter out weights & criterias for removed sections on server side
        if (!empty($removed)) {
            foreach ($removed as $removedSectionId) {
                unset($weights[$removedSectionId], $criterias[$removedSectionId]);
            }
        }

        // Validate total weight ~100 (allow tiny rounding tolerance 0.01)
        $totalWeight = 0.0;
        foreach ($weights as $w) {
            $totalWeight += floatval($w);
        }
        if (abs($totalWeight - 100.0) > 0.05) { // tolerance
            return back()->withInput()->with('error', 'Section weights must total 100.00. Current total: ' . number_format($totalWeight, 2));
        }

        DB::beginTransaction();
        try {
            // 1. Deactivate removed sections (keep historical record)
            if (!empty($removed)) {
                RFQSection::where('RFQID', $rfqId)
                    ->whereIn('SectionID', $removed)
                    ->update([
                        'IsActive' => false,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
            }

            // 2. Upsert remaining sections with provided weights and mark active
            foreach ($weights as $sectionId => $weight) {
                $sectionModel = RFQSection::firstOrNew([
                    'RFQID' => $rfqId,
                    'SectionID' => $sectionId,
                ]);
                $isNew = !$sectionModel->exists;
                $sectionModel->Weight = floatval($weight);
                $sectionModel->IsActive = true;
                $sectionModel->ModifiedBy = Auth::id();
                $sectionModel->ModifiedOn = now();
                if ($isNew) {
                    $sectionModel->CreatedBy = Auth::id();
                    $sectionModel->CreatedOn = now();
                }
                $sectionModel->save();
            }

            // 3. Remove existing RFQCriteria for removed or updated sections then recreate
            $affectedSectionIds = array_keys($weights);
            if (!empty($removed)) {
                $affectedSectionIds = array_merge($affectedSectionIds, $removed);
            }
            if (!empty($affectedSectionIds)) {
                RFQCriteria::where('RFQID', $rfqId)
                    ->whereIn('SectionID', $affectedSectionIds)
                    ->delete();
            }

            // 4. Insert criteria rows for sections still active
            foreach ($criterias as $sectionId => $criteriaIds) {
                $weight = floatval($weights[$sectionId] ?? 0);
                if (empty($criteriaIds)) continue;
                foreach ($criteriaIds as $criteriaId) {
                    RFQCriteria::create([
                        'RFQID' => $rfqId,
                        'SectionID' => $sectionId,
                        'CriteriaID' => $criteriaId,
                        'MaxScore' => $weight,
                        'IsActive' => true,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn(new RFQCriteria())
                ->withProperties([
                    'rfq_id' => $rfqId,
                    'removed_sections' => $removed,
                    'total_weight' => $totalWeight,
                ])
                ->log('Saved RFQ Evaluation Criteria (create/update)');

            DB::commit();
            return redirect()->route('rfqcriteriasetup.evaluations')->with('success', 'RFQ criteria setup saved.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save criteria: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        $criteria = RFQCriteria::findOrFail($id);
        $criteria->DeletedBy = Auth::id();
        $criteria->save();
        $criteria->delete();

        activity()
            ->performedOn($criteria)
            ->causedBy(Auth::user())
            ->log('Deleted RFQ Criteria');

        return back()->with('success', 'RFQ Criteria deleted successfully.');
    }
}
