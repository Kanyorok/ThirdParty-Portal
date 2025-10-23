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
use Illuminate\Http\JsonResponse;

class ReversingJournalController extends Controller
{
    //
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);

        // Build query with filters
        $query = FinanceJournalEntry::select('Id','Date','RefNo','Type','ApprovalStatus','Description','CreatedBy')
            ->with('reverseJournals:Id,JournalEntryId,OriginalReferenceNumber,OriginalJournalEntryID,ReversalDate,Reason,SystemDescription','createdBy:Id,Name')
            ->where('Type','reversing');

        // Apply filters if provided
        if ($request->filled('ref_no')) {
            $query->where('RefNo', 'like', '%' . $request->ref_no . '%');
        }

        if ($request->filled('original_ref')) {
            $query->whereHas('reverseJournals', function($q) use ($request) {
                $q->where('OriginalReferenceNumber', 'like', '%' . $request->original_ref . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereHas('reverseJournals', function($q) use ($request) {
                $q->whereDate('ReversalDate', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('reverseJournals', function($q) use ($request) {
                $q->whereDate('ReversalDate', '<=', $request->date_to);
            });
        }

        if ($request->filled('reason')) {
            $query->whereHas('reverseJournals', function($q) use ($request) {
                $q->where('Reason', 'like', '%' . $request->reason . '%');
            });
        }

        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('ApprovalStatus', $request->approval_status);
        }

        // Apply sorting
        $sortField = $request->sort_by ?? 'Date';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 10;
        $reversingJournals = $query->paginate($perPage)->withQueryString();

        // Get filter options for dropdowns
        $approvalStatuses = FinanceJournalEntry::distinct()
            ->where('Type', 'reversing')
            ->pluck('ApprovalStatus')
            ->filter()
            ->unique()
            ->values();

        return view('finance.generalledger.reversingjournal.index', compact(
            'reversingJournals',
            'approvalStatuses'
        ));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        
        // Get IDs of journals that are already reversed
        $reversedJournalIds = FinanceJournalEntry::where('IsReversed', true)->pluck('Id');
        
        // Get IDs of original journals that have a pending reversal (draft reversing journal)
        $pendingReversalOriginalIds = ReverseJournalEntry::whereHas('journalEntry', function ($query) {
            $query->where('ApprovalStatus', 'draft');
        })->pluck('OriginalJournalEntryID');

        // Get journals that can be reversed (posted, not reversed, no pending reversal, not already reversing journals)
        $journalEntries = FinanceJournalEntry::select('Id', 'RefNo', 'Description', 'Type')
            ->where('ApprovalStatus', 'posted')
            ->where('Type', '!=', 'reversing')
            ->whereNotIn('Id', $reversedJournalIds)
            ->whereNotIn('Id', $pendingReversalOriginalIds)
            ->orderByDesc('Id')
            ->get();

        // Get journals with pending reversals (to show as disabled)
        $pendingEntries = FinanceJournalEntry::select('Id', 'RefNo', 'Description', 'Type')
            ->whereIn('Id', $pendingReversalOriginalIds)
            ->orderByDesc('Id')
            ->get();

        return view('finance.generalledger.reversingjournal.create', compact('journalEntries', 'pendingEntries'));
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
            return redirect()->route('reversingjournal.index')
                ->with('success','Reverse Journal Entry created successfully.');
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
        $journalEntry = FinanceJournalEntry::with([
            'journalLines.glAccount',
            'sourceModule',
            'createdBy:Id,Name',
            'modifiedBy:Id,Name',
            'reversalsAsOriginal' => function($query) {
                $query->with('journalEntry.createdBy:Id,Name');
            }
        ])->findOrFail($id);
        return view('finance.generalledger.reversingjournal.show', compact('journalEntry','originalJournalRef'));;
    }

    // AJAX: preview original journal by id
    public function preview($id): JsonResponse
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);
        $je = FinanceJournalEntry::with(['journalLines.glAccount:id,GLName,GLCode'])->findOrFail($id);
        return response()->json([
            'Id' => $je->Id,
            'RefNo' => $je->RefNo,
            'Date' => $je->Date,
            'Type' => $je->Type,
            'Lines' => $je->journalLines->map(function($l){
                return [
                    'GLName' => ($l->glAccount? ($l->glAccount->GLCode.' ('.$l->glAccount->GLName.')') : ''),
                    'Debit' => (float)($l->Debit ?? 0),
                    'Credit' => (float)($l->Credit ?? 0),
                    'Amount' => (float)($l->Amount ?? 0),
                    'Narration' => $l->Narration,
                ];
            }),
        ]);
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
