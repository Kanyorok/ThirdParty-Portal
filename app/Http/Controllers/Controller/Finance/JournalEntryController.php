<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\HRM\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class JournalEntryController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);
        $journalEntries = FinanceJournalEntry::with('journalLines:Id,JournalEntryId,Debit,Credit,Amount,IsDebit,Narration')
            ->select('Id','RefNo','Date','Description','ApprovalStatus','Type')->where('Type','normal')->get();
        return view('finance.generalledger.journalentry.index',compact('journalEntries'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        $gls = FinanceGLAccounts::select('Id', 'GLName')->get();
        $branches = Branch::select('Id', 'Name')->get();
        $departments = Department::select('Id', 'Name')->get();
        return view('finance.generalledger.journalentry.create', compact('gls', 'branches', 'departments'));
    }


    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        // Reconstruct the entries array to help in checking if CR wquals DR
        $entries = [];
        foreach ($request->GLAccount as $index => $gl) {
            $entries[] = [
                'gl_id' => $gl,
                'branch_id' => $request->Branch[$index],
                'department_id' => $request->Department[$index],
                'debit' => $request->DRCR[$index] === 'DR' ? $request->Amount[$index] : 0,
                'credit' => $request->DRCR[$index] === 'CR' ? $request->Amount[$index] : 0,
                'is_debit' => $request->DRCR[$index] === 'DR' ? true : false,
                'amount'=> $request->Amount[$index],
                'narration' => $request->Narration[$index] ?? null,
            ];
        }

        // Merge the normalized structure into the request
        $request->merge(['entries' => $entries]);

        // Now validate
        $validated = $request->validate([
            'JournalDate' => 'required|date',
            'Description' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.gl_id' => 'required|exists:t_FinanceGLAccounts,Id',
            'entries.*.branch_id' => 'required|exists:t_Branches,Id',
            'entries.*.department_id' => 'required|exists:t_Departments,Id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.credit' => 'required|numeric|min:0',
        ]);

        // Check if total DR equals total CR
        $totalDebit = collect($validated['entries'])->sum('debit');
        $totalCredit = collect($validated['entries'])->sum('credit');

        if ($totalDebit != $totalCredit) {
            return back()->withErrors(['Amount mismatch' => 'Total Debit must equal Total Credit'])->withInput();
        }

        DB::beginTransaction();
        try{
            // Save the master entry in the JournalEntry Table
            $journalEntry = FinanceJournalEntry::create([
                'Date' => $request->JournalDate,
                'Description' => $request->Description,
                'CreatedBy' => Auth::id(),
                'ModifiedBy'=> Auth::Id(),
            ]);

            // SAVE to the JournalLines table
            foreach ($request->entries as $entry) {
                FinanceJournalLines::create([
                    'JournalEntryId' => $journalEntry->Id,
                    'GLAccountID'    => $entry['gl_id'],
                    'BranchID'       => $entry['branch_id'],
                    'DepartmentID'   => $entry['department_id'],
                    'IsDebit'        => $entry['is_debit'],
                    'Amount'         => $entry['amount'],
                    'Debit'          => $entry['debit'] ?? 0,
                    'Credit'         => $entry['credit'] ?? 0,
                    'Narration'      => $entry['narration'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy'=> Auth::Id(),
                ]);
            }
            activity('Journal Entry Creation')
                ->performedOn(new FinanceJournalEntry())
                ->causedBy(Auth::id())
                ->withProperties(['Create' =>$journalEntry])
                ->log('Created Journal Entry');
            DB::commit();
            return back()->with('success', 'Journal Entry created successfully.');
            //return redirect()->route('journalentry.index')->with('success', 'Journal Entry created successfully.');
        }catch (\Throwable $th){
            DB::rollBack();
            //return $th->getMessage();
            Log::error('Failed to create Journal Entry'.$th->getMessage());
            return back()->with('error', 'Failed to create Journal Entry');
        }
    }


    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);
        $journalEntry = FinanceJournalEntry::with('journalLines.glAccount','createdBy:Id,Name')->findOrFail($id);
        return view('finance.generalledger.journalentry.show', compact('journalEntry'));
    }

    public function action(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'action_type' => 'required|in:approve,reject',
        ]);

        $journalEntry = FinanceJournalEntry::findOrFail($id);
        $journalEntry->ApprovalStatus = $request->action_type === 'approve' ? 'Posted' : 'Rejected';
        $journalEntry->save();

        // Optional: log the reason somewhere if needed
        // JournalApproval::create([...])

        return redirect()->route('journalentry.index')->with('status', 'Journal entry ' . $request->action_type . 'd successfully.');
    }


}
