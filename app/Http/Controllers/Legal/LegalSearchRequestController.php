<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalSearchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalSearchRequestController extends Controller
{
    public function index()
    {
        // $requests = LegalSearchRequest::orderByDesc('RequestDate')->get();
        return view('legal.search_requests.index');
    }

    public function create()
    {
        return view('legal.search_requests.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'RequestType' => 'required|string', // e.g., "Company", "Individual", "Group"
            'EntityName' => 'required|string',
            'EntityType' => 'nullable|string',
            'RegistrationNumber' => 'nullable|string',
            'Country' => 'nullable|string',
            'RequestDate' => 'required|date',
            'Remarks' => 'nullable|string',
        ]);

        $data['RequestedBy'] = Auth::id();
        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();
        $data['Status'] = 'PENDING';
        $data['IsActive'] = 1;

        LegalSearchRequest::create($data);

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
        return view('legal.search_requests.edit', compact('request'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'RequestType' => 'required|string',
            'EntityName' => 'required|string',
            'EntityType' => 'nullable|string',
            'RegistrationNumber' => 'nullable|string',
            'Country' => 'nullable|string',
            'RequestDate' => 'required|date',
            'Remarks' => 'nullable|string',
            'Status' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        LegalSearchRequest::where('ID', $id)->update($data);

        return redirect()->route('legal.search_requests.index')->with('success', 'Search request updated.');
    }

    public function destroy($id)
    {
        LegalSearchRequest::where('ID', $id)->update([
            'IsActive' => 0,
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now()
        ]);

        return back()->with('success', 'Search request deactivated.');
    }
}
