<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\Finance\ReverseJournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReversingJournalController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);
        $reversingJournals=FinanceJournalEntry::select('Id','Date','RefNo','Type','ApprovalStatus','Description','CreatedBy')
                                ->with('reverseJournals:Id,JournalEntryId,OriginalReferenceNumber,OriginalJournalEntryID,ReversalDate,Reason,SystemDescription','createdBy:Id,Name')
                                ->where('Type','reversing')->get();
        return view('finance.generalledger.reversingjournal.index',compact('reversingJournals'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        $journalEntries=FinanceJournalEntry::select('Id','RefNo','Description')->where('ApprovalStatus','posted')->get();
        return view('finance.generalledger.reversingjournal.create',compact('journalEntries'));
    }

    public function store(Request $request){
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        $validated = $request->validate([
            'OriginalJournalID' => 'required|exists:t_FinanceJournalEntries,Id',
            'ReversalDate' => 'required|date|after_or_equal:today',
            'Reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
        $originalJournal=FinanceJournalEntry::findOrFail($validated['OriginalJournalID']);
            //Store in the journal entry table
            $journalEntry = FinanceJournalEntry::create([
                'Date' => $validated['ReversalDate'],
                'Description'    => $validated['Reason'],
                'Type'=> 'reversing',
                'CreatedBy' => Auth::id(),
                'ModifiedBy'=> Auth::Id(),
            ]);
            //Store in the Reverse Journal Table
            ReverseJournalEntry::create([
                'JournalEntryId'=> $journalEntry->Id,
                'OriginalReferenceNumber' => $originalJournal->RefNo,
                'OriginalJournalEntryID' => $originalJournal->Id,
                'ReversalDate' => $validated['ReversalDate'],
                'Reason' => $validated['Reason'],
                'SystemDescription'=>'Reversing Entry for Journal #' . $journalEntry->RefNo,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);
            //Reversing the lines in the JournalLines Table
            //Fetch the lines for the original Journal
            $lines=FinanceJournalLines::where('JournalEntryId',$originalJournal->Id)->get();
            //Reverse those lines by creating new lines
            foreach ($lines as $line) {
                FinanceJournalLines::create([
                    'JournalEntryId' => $journalEntry->Id,
                    'GLAccountID'    => $line->GLAccountID,
                    'BranchID'       => $line->BranchID,
                    'DepartmentID'   => $line->DepartmentID,
                    'IsDebit'        => !$line->IsDebit, // Flip
                    'Amount' => !$line->IsDebit ? $line->Amount * -1 : $line->Amount,
                    'Debit' => $line->Credit * -1,    // Flip
                    'Credit' => abs($line->Debit),     // Flip
                    'Narration'      => 'Reversal: ' . ($line->Narration ?? ''),
                    'CreatedBy'      => Auth::id(),
                    'ModifiedBy'     => Auth::id(),
                ]);
            }
            activity('Reversing Journal Entry')
                ->performedOn(new FinanceJournalEntry())
                ->causedBy(Auth::id())
                ->withProperties(['Original Reference Number' =>$originalJournal->RefNo])
                ->log('Reversing Journal Entry');
            DB::commit();
            return back()->with('success','Reverse Journal Entry created successfully.');
        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Error storing Reverse Journal Entry: '.$th);
            return $th->getMessage();
            return back()->with('error','Error storing Reverse Journal Entry');
        }

    }

    public function show($id){
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, ReverseJournalEntry::class);
        $originalJournalRef=ReverseJournalEntry::where('JournalEntryId',$id)->pluck('OriginalReferenceNumber')->first();
        $journalEntry = FinanceJournalEntry::with('journalLines.glAccount','createdBy:Id,Name')->findOrFail($id);
        return view('finance.generalledger.reversingjournal.show', compact('journalEntry','originalJournalRef'));;
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerDelete, FinanceJournalEntry::class);

        $entry = FinanceJournalEntry::findOrFail($id);
        DB::transaction(function () use ($entry) {
            // Delete related lines and reverse journal record
            FinanceJournalLines::where('JournalEntryId', $entry->Id)->delete();
            ReverseJournalEntry::where('JournalEntryId', $entry->Id)->delete();
            $entry->delete();
        });

        return redirect()->route('reversingjournal.index')->with('success', 'Reversing journal deleted.');
    }
}
