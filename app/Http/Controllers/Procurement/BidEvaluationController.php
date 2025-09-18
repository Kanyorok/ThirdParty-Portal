<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\procurement\Criteria;
use App\Models\Procurement\Tender;
use App\Models\procurement\TenderCriteria;
use App\Models\procurement\TenderSection;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;

class BidEvaluationController extends Controller
{
    //

    public function index(Request $request)
    {
        $supplierID = $request->sID;
        $TenderId = $request->tenderId;

        $tender = Tender::findOrFail($TenderId);
        // Fetch all active criteria already stored for this tender
        $existingCriteria = TenderCriteria::where('TenderID', $TenderId)
            ->where('IsActive', true)
            ->pluck('CriteriaID')
            ->toArray();

        // Get sections associated with the tender along with their criteria
        $tenderSections = TenderSection::where('TenderID', $tender->Id)
            ->with('sections') // We’ll handle criteria manually
            ->get();

        foreach ($tenderSections as $section) {
            // Get criteria manually for this section
            $criteriaList = Criteria::where('SectionID', $section->sections->id)->get();

            // Add `isChecked` to each criterion
            foreach ($criteriaList as $criteria) {
                $criteria->isChecked = isset($existingCriteria) && in_array($criteria->id, $existingCriteria);
            }

            // Attach the criteria list to the section manually
            $section->criteria = $criteriaList;
        }

        // Check if this supplier's bid is responsive before allowing evaluation
        $tenderSupplier = TenderSupplier::where('TenderID', $TenderId)
            ->where('SupplierID', $supplierID)
            ->with('bidResponsiveness')
            ->first();
            
        if (!$tenderSupplier || !$tenderSupplier->bidResponsiveness || !$tenderSupplier->bidResponsiveness->IsResponsive) {
            return redirect()->back()->with('error', 'This bid must be marked as responsive before it can be evaluated. Please complete the responsiveness check first.');
        }

        $supplier = Supplier::find($supplierID)->SupplierName ?? 'Unknown Supplier';
        return view('procurement.tendering.bidopeningandevaluation.evaluation.evaluate', compact(
            'TenderId',
            'tender',
            'tenderSections',
            'supplier',
            'tenderSupplier'
        ));

    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.evaluation.create');
    }

}
