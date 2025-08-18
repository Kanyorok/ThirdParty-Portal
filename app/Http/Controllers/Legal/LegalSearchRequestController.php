<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Legal\LegalSearchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalSearchRequestController extends Controller
{
    public function index()
    {
        $requests = LegalSearchRequest::orderByDesc('RequestDate')->get();
        return view('legal.search_requests.index', compact('requests'));
    }

    public function create()
    {
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'LegalSearchRequestTypes')  
            ->get();
        return view('legal.search_requests.create', compact('details'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'RequestType' => 'required|exists:t_CodeDetails,Value', // e.g., "Company", "Individual", "Group"
            'EntityName' => 'required|string',
            // 'EntityType' => 'nullable|string',
            // 'RegistrationNumber' => 'nullable|string',
            // 'Country' => 'nullable|string',
            // 'RequestDate' => 'required|date',
            'Remarks' => 'nullable|string',
        ]);

        $requestdate = now();
        $request = LegalSearchRequest::create([
            'RequestType'=> $validated['RequestType'],
            'EntityName'=> $validated['EntityName'],
            // 'EntityType'=> $validated['EntityType'],
            // 'RegistrationNumber'=> $validated['RegistrationNumber'],
            // 'Country'=> $validated['Country'],
            // 'RequestDate'=> $validated['RequestDate'],
            'Remarks'=> $validated['Remarks'],
            'Status'=> $validated['Status'] ?? 'pending',
            // 'IsActive'=> $validated['IsActive'] ?? false,
            'RequestDate' =>$requestdate,
            'RequestedBy' => Auth::id(),
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.search_requests.index')->with('success', 'Search request submitted.');
    }

    public function show($id)
    {
        $request = LegalSearchRequest::findOrFail($id);
        return view('legal.search_requests.show', compact('request'));
    }

    public function edit($id)
    {
        $request = LegalSearchRequest::findOrFail($id);
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'LegalSearchRequestTypes')  
            ->get();
        return view('legal.search_requests.edit', compact('request', 'details'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'RequestType' => 'required|string',
            'EntityName' => 'required|string',
            'EntityType' => 'nullable|string',
            'Remarks' => 'nullable|string',
            'Status' => 'nullable|string',
        ]);
        $requestdate = now();
        $data['RequestDate'] = $requestdate;
        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();


        LegalSearchRequest::where('ID', $id)->update($data);

        return redirect()->route('legal.search_requests.index')->with('success', 'Search request updated.');
    }

    public function destroy($id)
    {
        $searches = LegalSearchRequest::findOrFail($id);
        $searches->DeletedBy = Auth::id();
        $searches->save();
        $searches->delete();

        return back()->with('success', 'Search request deactivated.');
    }

    public function storeApprovalStatus(Request $request, $id)
    {
        $searchRequests = LegalSearchRequest::findOrFail($id);

        $validated = $request->validate([
            'Status' => 'required|string',
            'Findings' => 'nullable|string',
            'ApprovalReason' => 'nullable|string',
        ]);
        $searchRequests->update([
            'Status' => $validated['Status'],
            'Findings' => $validated['Findings'],
            'ApprovalReason' => $validated['ApprovalReason'],
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('legal.search_requests.index')->with('success', 'Search request status updated.');
    }
}
