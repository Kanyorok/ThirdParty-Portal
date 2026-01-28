<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnderwritingController extends Controller
{
    // Show all submitted proposals pending review
    public function index()
    {
        $proposals = DB::table('t_BancassurancePolicies as p')
            ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
            ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
            ->select(
                'p.*',
                'c.FullName as CustomerName',
                'prod.Name as ProductName'
            )
            ->where('p.Status', 'Proposal') // Only proposals pending review
            ->orderByDesc('p.Id')
            ->get();

        return view('bancassurance.underwriting.index', compact('proposals'));
    }

    // Show single proposal for underwriting decision
    public function review($id)
    {
        $policy = DB::table('t_BancassurancePolicies as p')
            ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
            ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
            ->select(
                'p.*',
                'c.FullName as CustomerName',
                'prod.Name as ProductName'
            )
            ->where('p.Id', $id)
            ->first();

        if (! $policy) {
            abort(404, 'Policy not found');
        }

        return view('bancassurance.underwriting.review', compact('policy'));
    }

    // Store underwriting decision
    public function submit(Request $request, $id)
    {
        $request->validate([
            'UnderwriterComments' => 'required|string',
            'RiskRating' => 'required|string',
            'Decision' => 'required|string',
        ]);

        // Insert into underwriting table
        DB::table('t_BancassuranceUnderwriting')->insert([
            'PolicyID' => $id,
            'UnderwriterComments' => $request->UnderwriterComments,
            'RiskRating' => $request->RiskRating,
            'Decision' => $request->Decision,
            'DecisionDate' => now(),
            'DecidedBy' => auth()->id(),
            'CreatedAt' => now(),
        ]);

        // Update policy status based on decision
        $status = match ($request->Decision) {
            'Approved' => 'Underwritten',
            'Declined' => 'Declined',
            default => 'Pending Clarification',
        };

        DB::table('t_BancassurancePolicies')
            ->where('Id', $id)
            ->update([
                'Status' => $status,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);

        return redirect()->route('bancassurance.underwriting.index')
            ->with('success', 'Underwriting decision submitted.');
    }
}
