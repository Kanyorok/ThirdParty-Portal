<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Legal\LoanSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanSecurityController extends Controller
{
    public function index()
    {
        $securities = LoanSecurity::select('Id', 'SecurityType', 'OwnerName', 'LoanAccountNumber', 'Value', 'Institution', 'SecurityStatus')->get();
        return view('legal.securities.index', compact('securities'));
    }

    public function create()
    {
        $details = CodeDetail::select('Value')
            ->where('CodeID','LoanSecurityTypes')
            ->get();
        $locations = CodeDetail::select('Value')
            ->where('CodeID','LoanSecurityLocations')
            ->get();
        return view('legal.securities.create', compact('details','locations'));
    }

    public function store(Request $request)
    {
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
        ]);

        $securities = LoanSecurity::create([
            'SecurityType'=> $validated['SecurityType'],
            'OwnerName'=> $validated['OwnerName'],
            'OwnerIDNumber'=> $validated['OwnerIDNumber'],
            'LoanAccountNumber'=> $validated['LoanAccountNumber'],
            'Value'=> $validated['Value'],
            'Institution'=> $validated['Institution'],
            'RegistrationDetails'=> $validated['RegistrationDetails'],
            'Locations'=> $validated['Locations'],
            'Remarks'=> $validated['Remarks'],
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.securities.index')->with('success', 'Security registered successfully.');
    }

    public function edit($id)
    {
        $details = CodeDetail::select('Value')
            ->where('CodeID','LoanSecurityTypes')
            ->get();
        $locations = CodeDetail::select('Value')
            ->where('CodeID','LoanSecurityLocations')
            ->get();
        $security = LoanSecurity::findOrFail($id);
        return view('legal.securities.edit', compact('security', 'details', 'locations'));
    }

    public function update(Request $request, $id)
    {
        // $this->authorize(PermissionEnum::LoanSecurityUpdate, LoanSecurity::class);

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

        // try {
        //     DB::beginTransaction();

            $userId = Auth::id();
            $now = now();

            $security = LoanSecurity::findOrFail($id);

            $security->update([
                'SecurityType'        => $validated['SecurityType'],
                'OwnerName'           => $validated['OwnerName'],
                'OwnerIDNumber'       => $validated['OwnerIDNumber'],
                'LoanAccountNumber'   => $validated['LoanAccountNumber'],
                'Value'               => $validated['Value'],
                'Institution'         => $validated['Institution'],
                'RegistrationDetails' => $validated['RegistrationDetails'],
                'Locations'           => $validated['Locations'],
                'SecurityStatus'      => $validated['SecurityStatus'],
                'Remarks'             => $validated['Remarks'],
                'ModifiedBy'          => $userId,
                'ModifiedOn'          => $now,
            ]);

            // activity()
            //     ->performedOn($security)
            //     ->causedBy(Auth::user())
            //     ->withProperties(['action' => 'update'])
            //     ->log('Updated a loan security');

            // DB::commit();

            // dd($security);
         return redirect()->route('legal.securities.index')->with('success', 'Security updated successfully.');
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     Log::error('Failed to update loan security.', [
        //         'error' => $th->getMessage(),
        //         'stack' => $th->getTraceAsString(),
        //     ]);
        //     return back()->with('error', 'An error occurred while updating the loan security. Please try again.');
        // }
    }


    public function show($id)
    {
        $security = LoanSecurity::findOrFail($id);
        return view('legal.securities.show', compact('security'));
    }

    public function destroy($id)
    {
        $security = LoanSecurity::findOrFail($id);

        $security->DeletedBy = Auth::id();
        $security->save();
        $security->delete();

        return back()->with('success','Loan Security successfully deleted');

    }

}
