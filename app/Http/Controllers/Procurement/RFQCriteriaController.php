<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCriteria;
use App\Models\Procurement\RFQSection;
use App\Models\Procurement\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQCriteriaController extends Controller
{
    public function show($rfqId)
    {
        $rfq = RFQ::findOrFail($rfqId);

        // Get assigned RFQ sections with their criteria from t_Criterias via Section
        $rfqSections = RFQSection::with(['section.criteria'])->where('RFQID', $rfqId)->get();

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


        return view('procurement.rfqcriteriasetup.rfqCriteria', [
            'rfq' => $rfq,
            'rfqSections' => $rfqSections,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required|exists:t_RFQ,Id',
            'weights' => 'required|array',
            'criterias' => 'required|array',
        ]);

        $rfqId = $request->rfq_id;

        DB::beginTransaction();
        try {
            // Delete existing criteria
            RFQCriteria::where('RFQID', $rfqId)->delete();

            foreach ($request->criterias as $sectionId => $criteriaList) {
                $weight = $request->weights[$sectionId] ?? 0;

                foreach ($criteriaList as $criteriaId) {
                    RFQCriteria::create([
                        'RFQID' => $rfqId,
                        'SectionID' => $sectionId,
                        'CriteriaID' => $criteriaId,
                        'MaxScore' => $weight,
                        'IsActive' => true,
                        'CreatedBy' => auth()->id(),
                        'ModifiedBy' => auth()->id(),
                    ]);
                }
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn(new RFQCriteria())
                ->log('Updated RFQ Evaluation Criteria for RFQ ID: ' . $rfqId);

            DB::commit();
            return redirect()->route('rfqcriteriasetup.evaluations')->with('success', 'RFQ criteria setup saved.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to save criteria: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        $criteria = RFQCriteria::findOrFail($id);
        $criteria->DeletedBy = auth()->id();
        $criteria->save();
        $criteria->delete();

        activity()
            ->performedOn($criteria)
            ->causedBy(auth()->user())
            ->log('Deleted RFQ Criteria');

        return back()->with('success', 'RFQ Criteria deleted successfully.');
    }
}
