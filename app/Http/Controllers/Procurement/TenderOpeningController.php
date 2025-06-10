<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\Tender;
use Illuminate\Http\Request;

class TenderOpeningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetching all tender openings can be done here if needed
        //return Tender::all();
        $tenders = Tender::select('Id', 'TenderNo', 'Title', 'Status')
            ->where('Status', '=', 'pb')
            ->get();
        $data = false;
        return view('procurement.tendering.bidopeningandevaluation.opening.index', compact('tenders', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.opening.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $submissions = BidSubmission::where('TenderRef', $id)
            ->with(['createdByUser', 'modifiedByUser']) // Eager load users
            ->select('Id', 'SupplierName', 'SubmissionMode', 'ReceivedAt', 'CreatedBy', 'Remarks', 'ModifiedBy')
            ->get();

        $tenders = Tender::select('Id', 'TenderNo', 'Title', 'Status')
            ->where('Status', '=', 'pb')
            ->get();
        $data = true; // This variable is used to indicate that there are no submissions yet
        return view('procurement.tendering.bidopeningandevaluation.opening.index', compact('tenders', 'submissions', 'data'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
