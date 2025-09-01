<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseCounsel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalCounselController extends Controller
{
    public function index($caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCaseCounsel::class);

        $case = LegalCase::findOrFail($caseId);
        $counsels = LegalCaseCounsel::where('LegalCaseID', $caseId)->get();

        return view('legal.disputes.counsels.index', compact('case', 'counsels'));
    }

    public function create($caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCaseCounsel::class);

        $case = LegalCase::findOrFail($caseId);

        return view('legal.disputes.counsels.create', compact('case'));
    }

    public function store(Request $request, $caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCaseCounsel::class);

        $validated = $request->validate([
            'LegalCaseID' => 'required|exists:t_LegalCases,Id',
            'CounselName' => 'required|string|max:255',
            'FirmName' => 'required|string|max:255',
            'Email' => 'required|email|max:255',
            'Phone' => [
                'required',
                'regex:/^\+2547\d{8}$/', // must be +2547xxxxxxxx
            ],
            'Role' => 'required|string|max:100',
            // 'Remarks' => 'nullable|string',
        ]);

        $duplicates = LegalCaseCounsel::where('Email', $validated['Email'])
            ->where('Phone', $validated['Phone'])
            ->exists();

        if($duplicates){
            return back()->with('error', 'A counsel with this details already exists');
        }
        try{
            DB::beginTransaction();

            $counsel =  LegalCaseCounsel::create([
                'LegalCaseID' => $caseId,
                'CounselName' => $validated['CounselName'],
                'FirmName' => $validated['FirmName'] ?? null,
                'Email' => $validated['Email'],
                'Phone' => $validated['Phone'],
                'Role' => $validated['Role'],
                // 'Remarks' => $validated['Remarks'],
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
            // return $counsel;
            activity()
            ->performedOn(new LegalCaseCounsel())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'create'])
            ->log('Legal Counsel created');

            DB::commit();

            return redirect()->route('legal.cases.show', $caseId)->with('success', 'Counsel assigned successfully.');
        }catch(\Throwable $th){
            DB::rollBack();

            activity()
                ->performedOn(new LegalCaseCounsel())
                ->causedBy(Auth::user())
            ->withProperties(['action' => 'create'])
                ->log('Error creating legal councel: ' . $th->getMessage());

            Log::error('Error creating legal councel: ' . $th->getMessage());
            return back()->with('error', 'Error creating legal councel: ' . $th->getMessage());
        }
    }

    public function edit($disputeId, $counselId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCaseCounsel::class);

        $case = LegalCase::findOrFail($disputeId);
        $counsel = LegalCaseCounsel::where('LegalCaseID', $disputeId)
                                ->findOrFail($counselId);

        return view('legal.disputes.counsels.edit', compact('case', 'counsel'));
    }

    public function update(Request $request, $disputeId, $counselId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCaseCounsel::class);

        $case = LegalCase::findOrFail($disputeId);
        $counsel = LegalCaseCounsel::where('LegalCaseID', $disputeId)
                                ->findOrFail($counselId);

        $data = $request->validate([
            'CounselName' => 'required|string|max:255',
            'FirmName'    => 'required|string|max:255',
            'Email'       => 'required|email|max:255',
            'Phone' => [
                'required',
                'regex:/^\+2547\d{8}$/', // must be +2547xxxxxxxx
            ],
            'Role'        => 'required|string|max:100',
            'Remarks'     => 'required|string',
        ]);
        try{

            DB::beginTransaction();


            $counsel->update([
                'CounselName' => $data['CounselName'],
                'FirmName' => $data['FirmName'] ?? null,
                'Email' => $data['Email'],
                'Phone' => $data['Phone'],
                'Role' => $data['Role'],
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn(new LegalCaseCounsel)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Succefully Updated counsel: ' . $counsel->$data['CounselName']);

            DB::commit();

            return redirect()
                ->route('legal.disputes.counsels.show', [$case->Id, $counsel->Id])
                ->with('success', 'Counsel updated successfully.');
            }catch(\Throwable $th){
                DB::rollBack();

                activity()
                    ->performedOn(new LegalCaseCounsel())
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'update'])
                    ->log('Error editing legal councel: ' . $th->getMessage());

                Log::error('Error editing legal councel: ' . $th->getMessage());
                return back()->with('error', 'Error creating legal councel: ' . $th->getMessage());
        }
    }


    public function show($disputeId, $counselId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCaseCounsel::class);

        $case = LegalCase::findOrFail($disputeId);
        $counsel = LegalCaseCounsel::where('LegalCaseID', $disputeId)
                                ->findOrFail($counselId);

        return view('legal.disputes.counsels.show', compact('case', 'counsel'));
    }


    public function destroy($id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationDelete, LegalCaseCounsel::class);
        try{
            DB::beginTransaction();

            $counsel = LegalCaseCounsel::findOrFail($id);
            $caseId = $counsel->LegalCaseID;

            $counsel->DeletedBy = Auth::id();
            $counsel->save();
            $counsel->delete();

            activity()
                ->performedOn(new LegalCaseCounsel)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Counsel Succefully Deleted');

            return redirect()->route('legal.cases.show', $caseId)->with('success', 'Counsel removed successfully.');
        }catch(\Throwable $th){
            DB::rollBack();

            activity()
                    ->performedOn(new LegalCaseCounsel())
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'delete'])
                    ->log('Error deleting legal councel: ' . $th->getMessage());

            return back()->with('error', 'Error deleting Assigned counsel');
        }

    }
}
