<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\TenderSupplier;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use App\Models\Procurement\BidResponsiveness;
use Illuminate\Support\Facades\Auth;

class TenderBidResponsivenessController extends Controller
{
    public function index(Request $request)
    {

        $query = TenderSupplier::with(['tender', 'supplier', 'bidResponsiveness']);

        if ($request->filled('tender_filter')) {
            $query->whereHas('tender', function ($q) use ($request) {
                $q->where('TenderNo', $request->tender_filter);
            });
        }

        if ($request->filled('responsiveness_filter')) {
            $query->whereHas('bidResponsiveness', function ($q) use ($request) {
                $q->where('IsResponsive', $request->responsiveness_filter);
            });
        }

        if ($request->filled('search')) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where('SupplierName', 'like', '%' . $request->search . '%');
            });
        }

        // Get results
        $bidResponses = $query->get();

        // Distinct tender list for dropdown
        $tenders = Tender::whereIn('Id', TenderSupplier::select('TenderID')
        )->pluck('TenderNo');


        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.index', compact('bidResponses', 'tenders'))
            ->with([
                'filters' => $request->only(['tender_filter', 'responsiveness_filter', 'search']),
            ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenderSupplierID' => 'required|exists:t_TenderSuppliers,Id',
            'SubmittedTimely' => 'required|boolean',
            'HasMandatoryDocuments' => 'required|boolean',
            'IsEligible' => 'required|boolean',
            'IsResponsive' => 'required|boolean',
            'Remarks' => 'nullable|string',
        ]);

        $userId = Auth::id();

        $bid = BidResponsiveness::firstOrNew([
            'TenderSupplierID' => $request->TenderSupplierID
        ]);

        $isNew = !$bid->exists;
        $updated = false;

        foreach (['SubmittedTimely', 'HasMandatoryDocuments', 'IsEligible', 'IsResponsive', 'Remarks'] as $field) {
            if ($request->has($field) && $bid->$field !== $request->$field) {
                $bid->$field = $request->$field;
                $updated = true;
            }
        }

        if ($isNew) {
            $bid->CreatedBy = $userId;
            $updated = true;
        }

        if ($updated) {
            $bid->ModifiedBy = $userId;
            $bid->save();

            activity()
                ->causedBy($userId)
                ->performedOn($bid)
                ->event($isNew ? 'created' : 'updated')
                ->log(($isNew ? 'Created' : 'Updated') . ' bid responsiveness for supplier ID: ' . $bid->TenderSupplierID);

            $message = $isNew
                ? 'Bid responsiveness recorded successfully.'
                : 'Bid responsiveness updated successfully.';
        } else {
            $message = 'No changes were made.';
        }

        return redirect()
            ->route('bidresponsiveness.index')
            ->with('success', $message);
    }

    public function create(TenderSupplier $tenderSupplier)
    {
        $tenderSupplier->load('supplier');

        $bid = BidResponsiveness::where('TenderSupplierID', $tenderSupplier->getKey())->first();

        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.create', [
            'tenderSupplier' => $tenderSupplier,
            'bid' => $bid,
        ]);
    }
}
