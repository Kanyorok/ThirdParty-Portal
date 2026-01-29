<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Legal\LegalSearchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalSearchRequestController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::LegalSearchView, LegalSearchRequest::class);

        $requests = LegalSearchRequest::orderByDesc('RequestDate')->get();

        return view('legal.search_requests.index', compact('requests'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::LegalSearchCreate, LegalSearchRequest::class);

        $details = CodeDetail::select('Value')
            ->where('CodeID', 'LegalSearchRequestTypes')
            ->get();

        return view('legal.search_requests.create', compact('details'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::LegalSearchCreate, LegalSearchRequest::class);

        $validated = $request->validate([
            'RequestType' => 'required|exists:t_CodeDetails,Value', // e.g., "Company", "Individual", "Group"
            'EntityName' => 'required|string',
            // 'EntityType' => 'nullable|string',
            // 'RegistrationNumber' => 'nullable|string',
            // 'Country' => 'nullable|string',
            // 'RequestDate' => 'required|date',
            'Remarks' => 'required|string',
        ]);

        $duplicate = LegalSearchRequest::where('RequestType', $validated['RequestType'])
            ->where('EntityName', $validated['EntityName'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'There is already an existinf search request with this details');
        }

        try {
            DB::beginTransaction();

            $requestdate = now();
            $request = LegalSearchRequest::create([
                'RequestType' => $validated['RequestType'],
                'EntityName' => $validated['EntityName'],
                // 'EntityType'=> $validated['EntityType'],
                // 'RegistrationNumber'=> $validated['RegistrationNumber'],
                // 'Country'=> $validated['Country'],
                // 'RequestDate'=> $validated['RequestDate'],
                'Remarks' => $validated['Remarks'],
                'Status' => $validated['Status'] ?? 'Pending',
                // 'IsActive'=> $validated['IsActive'] ?? false,
                'RequestDate' => $requestdate,
                'RequestedBy' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn(new LegalSearchRequest())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Search Request created successfully');

            DB::commit();

            return redirect()->route('legal.search_requests.index')->with('success', 'Search Request submitted.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalSearchRequest())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Error creating Search Request');

            Log::error('Error creating Search Request' . $th->getMessage());

            return back()->with('error', 'Error creating Search Request: ' . $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::LegalSearchView, LegalSearchRequest::class);

        $request = LegalSearchRequest::findOrFail($id);

        return view('legal.search_requests.show', compact('request'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::LegalSearchUpdate, LegalSearchRequest::class);

        $request = LegalSearchRequest::findOrFail($id);
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'LegalSearchRequestTypes')
            ->get();

        return view('legal.search_requests.edit', compact('request', 'details'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::LegalSearchUpdate, LegalSearchRequest::class);

        $data = $request->validate([
            'RequestType' => 'required|string',
            'EntityName' => 'required|string',
            'Remarks' => 'required|string',
            'Status' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $requestdate = now();
            $data['RequestDate'] = $requestdate;
            $data['ModifiedBy'] = Auth::id();
            $data['ModifiedOn'] = now();


            LegalSearchRequest::where('Id', $id)->update($data);


            activity()
                ->performedOn(new LegalSearchRequest())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Search Request updated successfully');

            DB::commit();

            return redirect()->route('legal.search_requests.index')->with('success', 'Search request updated.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalSearchRequest())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Error updating Search Request');

            Log::error('Error updating Search Request' . $th->getMessage());

            return back()->with('error', 'Error updating Search Request: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::LegalSearchDelete, LegalSearchRequest::class);

        try {
            DB::beginTransaction();

            $searches = LegalSearchRequest::findOrFail($id);
            $searches->DeletedBy = Auth::id();
            $searches->save();
            $searches->delete();

            activity()
                ->performedOn(new LegalSearchRequest())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Search Request deleted successfully');

            DB::commit();

            return back()->with('success', 'Search request deleted.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalSearchRequest())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Error deleting Search Request');

            Log::error('Error deleting Search Request' . $th->getMessage());

            return back()->with('error', 'Error deleting Search Request: ' . $th->getMessage());
        }
    }

    public function storeApprovalStatus(Request $request, $id)
    {
        $searchRequests = LegalSearchRequest::findOrFail($id);

        $validated = $request->validate([
            'Status' => 'required|string|in:Approved,Rejected',
            'Findings' => 'nullable|string',
            'ApprovalReason' => 'nullable|string',
            'DocumentFile' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg',
        ], [
            'DocumentFile.mimes' => 'Only PDF, Word, Excel, CSV, JPG, and PNG files are allowed.',
            'DocumentFile.max' => 'File size must not exceed 5 MB.',
        ]);

        if ($validated['Status'] === 'Approved' && empty($validated['Findings'])) {
            return back()->withErrors(['Findings' => 'Findings are required when approving.'])->withInput();
        }

        if ($validated['Status'] === 'Rejected' && empty($validated['ApprovalReason'])) {
            return back()->withErrors(['ApprovalReason' => 'Rejection reason is required when rejecting.'])->withInput();
        }

        try {
            DB::beginTransaction();

            $searchRequests->update([
                'Status' => $validated['Status'],
                'Findings' => $validated['Findings'],
                'ApprovalReason' => $validated['ApprovalReason'],
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Upload document to DMS if file is provided
            if ($request->hasFile('DocumentFile')) {
                $searchRequests->newDocument(
                    ModulesEnum::Legal,
                    $request->file('DocumentFile'),
                    [PermissionEnum::LegalSearchView],
                    Auth::user()
                );

                activity()
                    ->performedOn($searchRequests)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'upload_document'])
                    ->log('Uploaded document with findings approval');
            }

            DB::commit();

            return redirect()->route('legal.search_requests.index')
                ->with('success', 'Search request status updated.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update search request status: ' . $th->getMessage());

            return back()->with('error', 'An error occurred. Please try again.');
        }
    }
}
