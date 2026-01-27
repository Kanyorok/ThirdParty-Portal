<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Legal\LoanSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanSecurityController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::LoanSecurityView, LoanSecurity::class);

        $securities = LoanSecurity::select('Id', 'SecurityType', 'OwnerName', 'LoanAccountNumber', 'Value', 'Institution', 'SecurityStatus')->get();

        return view('legal.securities.index', compact('securities'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::LoanSecurityCreate, LoanSecurity::class);

        $details = CodeDetail::select('Value')
            ->where('CodeID', 'LoanSecurityTypes')
            ->get();
        $locations = CodeDetail::select('Value')
            ->where('CodeID', 'LoanSecurityLocations')
            ->get();

        return view('legal.securities.create', compact('details', 'locations'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::LoanSecurityCreate, LoanSecurity::class);

        $validated = $request->validate([
            'SecurityType' => 'required|exists:t_CodeDetails,Value',
            'OwnerName' => 'required|string',
            'OwnerIDNumber' => 'required|string',
            'LoanAccountNumber' => 'required|string',
            'Value' => 'required|numeric',
            'Institution' => 'required|string',
            'RegistrationDetails' => 'required|string',
            'Locations' => 'required|exists:t_CodeDetails,Value',
            'Remarks' => 'required|string',
            'DocumentFile' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg',
        ], [
            'DocumentFile.mimes' => 'Only PDF, Word, Excel, CSV, JPG, and PNG files are allowed.',
            'DocumentFile.max' => 'File size must not exceed 5 MB.',
        ]);

        $duplicate = LoanSecurity::where('SecurityType', $validated['SecurityType'])
            ->where('OwnerIDNumber', $validated['OwnerIDNumber'])
            ->where('LoanAccountNumber', $validated['LoanAccountNumber'])
            ->where('RegistrationDetails', $validated['RegistrationDetails'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'Error there is an existing record with the same details');
        }

        try {
            DB::beginTransaction();

            $securities = LoanSecurity::create([
                'SecurityType' => $validated['SecurityType'],
                'OwnerName' => $validated['OwnerName'],
                'OwnerIDNumber' => $validated['OwnerIDNumber'],
                'LoanAccountNumber' => $validated['LoanAccountNumber'],
                'Value' => $validated['Value'],
                'Institution' => $validated['Institution'],
                'RegistrationDetails' => $validated['RegistrationDetails'],
                'Locations' => $validated['Locations'],
                'SecurityStatus' => $validated['SecurityStatus'] ?? 'Held',
                'Remarks' => $validated['Remarks'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            // Upload document to DMS if file is provided
            if ($request->hasFile('DocumentFile')) {
                $securities->newDocument(
                    ModulesEnum::Legal,
                    $request->file('DocumentFile'),
                    [PermissionEnum::LoanSecurityView],
                    Auth::user()
                );
            }

            activity()
                ->performedOn(new LoanSecurity())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Loan security successfully created');

            DB::commit();

            return redirect()->route('legal.securities.index')->with('success', 'Security registered successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LoanSecurity())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Error creating loan security');

            Log::error('Error creating loan security: ' . $th->getMessage());

            return back()->with('error', 'Error creating loan security' . $th->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::LoanSecurityUpdate, LoanSecurity::class);

        $details = CodeDetail::select('Value')
            ->where('CodeID', 'LoanSecurityTypes')
            ->get();
        $locations = CodeDetail::select('Value')
            ->where('CodeID', 'LoanSecurityLocations')
            ->get();
        $security = LoanSecurity::findOrFail($id);

        return view('legal.securities.edit', compact('security', 'details', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::LoanSecurityUpdate, LoanSecurity::class);

        $validated = $request->validate([
            'SecurityType' => 'required|exists:t_CodeDetails,Value',
            'OwnerName' => 'required|string',
            'OwnerIDNumber' => 'required|string',
            'LoanAccountNumber' => 'required|string',
            'Value' => 'required|numeric',
            'Institution' => 'required|string',
            'RegistrationDetails' => 'required|string',
            'Locations' => 'required|exists:t_CodeDetails,Value',
            'SecurityStatus' => 'required|string',
            'Remarks' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $now = now();

            $security = LoanSecurity::findOrFail($id);

            $security->update([
                'SecurityType' => $validated['SecurityType'],
                'OwnerName' => $validated['OwnerName'],
                'OwnerIDNumber' => $validated['OwnerIDNumber'],
                'LoanAccountNumber' => $validated['LoanAccountNumber'],
                'Value' => $validated['Value'],
                'Institution' => $validated['Institution'],
                'RegistrationDetails' => $validated['RegistrationDetails'],
                'Locations' => $validated['Locations'],
                'SecurityStatus' => $validated['SecurityStatus'],
                'Remarks' => $validated['Remarks'],
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
            ]);

            activity()
                ->performedOn(new LoanSecurity())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated a loan security');

            DB::commit();

            // dd($security);
            return redirect()->route('legal.securities.index')->with('success', 'Security updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            activity()
                ->performedOn(new LoanSecurity())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Error creating loan security');
            Log::error('Failed to update loan security.');

            return back()->with('error', 'An error occurred while updating the loan security. Please try again.');
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::LoanSecurityView, LoanSecurity::class);

        $security = LoanSecurity::findOrFail($id);

        return view('legal.securities.show', compact('security'));
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::LoanSecurityDelete, LoanSecurity::class);

        try {
            DB::beginTransaction();

            $security = LoanSecurity::findOrFail($id);

            $security->DeletedBy = Auth::id();
            $security->save();
            $security->delete();

            activity()
                ->performedOn(new LoanSecurity())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated a loan security');

            DB::commit();

            return back()->with('success', 'Loan Security successfully deleted');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LoanSecurity())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Error deleting loan security');

            Log::error('Error deleting loan security.' . $th->getMessage());

            return back()->with('error', 'Error deleting loan security.' . $th->getMessage());
        }
    }
}
