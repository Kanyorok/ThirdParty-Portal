<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidResponsiveness;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenderBidResponsivenessController extends Controller
{
    public function index(Request $request)
    {

        $query = TenderSupplier::with(['tender', 'supplier.thirdParty', 'bidResponsiveness']);

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
            $query->whereHas('supplier.thirdParty', function ($q) use ($request) {
                $q->where('ThirdPartyName', 'like', '%' . $request->search . '%');
            });
        }

        // Get results
        $bidResponses = $query->get();

        // Distinct tender list for dropdown
        $tenders = Tender::whereIn(
            'Id',
            TenderSupplier::select('TenderID')
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
            'TenderSupplierID' => $request->TenderSupplierID,
        ]);

        $isNew = ! $bid->exists;
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
        $tenderSupplier->load(['supplier.thirdParty', 'tender']);

        $bid = BidResponsiveness::where('TenderSupplierID', $tenderSupplier->getKey())->first();

        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.create', [
            'tenderSupplier' => $tenderSupplier,
            'bid' => $bid,
        ]);
    }

    /**
     * Display the specified bid responsiveness.
     */
    public function show(BidResponsiveness $bidResponsiveness)
    {
        $bidResponsiveness->load(['tenderSupplier.supplier', 'tenderSupplier.tender']);

        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.show', compact('bidResponsiveness'));
    }

    /**
     * Show the form for editing the specified bid responsiveness.
     */
    public function edit(BidResponsiveness $bidResponsiveness)
    {
        $tenderSupplier = $bidResponsiveness->tenderSupplier;
        $tenderSupplier->load(['supplier', 'tender']);

        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.edit', [
            'tenderSupplier' => $tenderSupplier,
            'bid' => $bidResponsiveness,
        ]);
    }

    /**
     * Update the specified bid responsiveness in storage.
     */
    public function update(Request $request, BidResponsiveness $bidResponsiveness)
    {
        $request->validate([
            'SubmittedTimely' => 'required|boolean',
            'HasMandatoryDocuments' => 'required|boolean',
            'IsEligible' => 'required|boolean',
            'IsResponsive' => 'required|boolean',
            'Remarks' => 'nullable|string|max:1000',
        ]);

        $updated = false;
        $userId = Auth::id();

        foreach (['SubmittedTimely', 'HasMandatoryDocuments', 'IsEligible', 'IsResponsive', 'Remarks'] as $field) {
            if ($request->has($field) && $bidResponsiveness->$field !== $request->$field) {
                $bidResponsiveness->$field = $request->$field;
                $updated = true;
            }
        }

        if ($updated) {
            $bidResponsiveness->ModifiedBy = $userId;
            $bidResponsiveness->save();

            activity()
                ->causedBy($userId)
                ->performedOn($bidResponsiveness)
                ->event('updated')
                ->log('Updated bid responsiveness for supplier ID: ' . $bidResponsiveness->TenderSupplierID);

            $message = 'Bid responsiveness updated successfully.';
        } else {
            $message = 'No changes were made.';
        }

        return redirect()
            ->route('bidresponsiveness.index')
            ->with('success', $message);
    }

    /**
     * Bulk update responsiveness for multiple bids
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'tender_id' => 'required|exists:t_Tenders,Id',
            'action' => 'required|in:approve_all,reject_all',
        ]);

        $tenderId = $request->tender_id;
        $action = $request->action;
        $userId = Auth::id();

        // Get all TenderSuppliers for this tender
        $tenderSuppliers = TenderSupplier::where('TenderID', $tenderId)->get();

        $updated = 0;
        foreach ($tenderSuppliers as $tenderSupplier) {
            $responsiveness = BidResponsiveness::firstOrNew([
                'TenderSupplierID' => $tenderSupplier->id,
            ]);

            if ($action === 'approve_all') {
                $responsiveness->SubmittedTimely = true;
                $responsiveness->HasMandatoryDocuments = true;
                $responsiveness->IsEligible = true;
                $responsiveness->IsResponsive = true;
                $responsiveness->Remarks = 'Bulk approved - meets all basic requirements';
            } else {
                $responsiveness->SubmittedTimely = false;
                $responsiveness->HasMandatoryDocuments = false;
                $responsiveness->IsEligible = false;
                $responsiveness->IsResponsive = false;
                $responsiveness->Remarks = 'Bulk rejected - does not meet basic requirements';
            }

            if (! $responsiveness->exists) {
                $responsiveness->CreatedBy = $userId;
            }
            $responsiveness->ModifiedBy = $userId;
            $responsiveness->save();
            $updated++;
        }

        return redirect()
            ->route('bidresponsiveness.index')
            ->with('success', "Bulk updated {$updated} bid responses successfully.");
    }
}
