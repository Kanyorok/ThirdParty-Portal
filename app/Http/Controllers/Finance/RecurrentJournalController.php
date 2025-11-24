<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\Finance\RecurrentJournal;
use App\Models\Finance\ReverseJournalEntry;
use App\Models\HRM\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurrentJournalController extends Controller
{
    //
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, FinanceJournalEntry::class);

        // Build query with filters
        $query = FinanceJournalEntry::with('recurringJournals:Id,JournalEntryId,StartDate,CuttOffDate,Frequency,ReferenceName,Description,NextRunDate')
            ->where('Type', 'recurring');

        // Apply filters if provided
        if ($request->filled('ref_no')) {
            $query->where('RefNo', 'like', '%' . $request->ref_no . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereHas('recurringJournals', function($q) use ($request) {
                $q->whereDate('StartDate', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('recurringJournals', function($q) use ($request) {
                $q->whereDate('StartDate', '<=', $request->date_to);
            });
        }

        if ($request->filled('reference_name')) {
            $query->whereHas('recurringJournals', function($q) use ($request) {
                $q->where('ReferenceName', 'like', '%' . $request->reference_name . '%');
            });
        }

        if ($request->filled('frequency') && $request->frequency !== 'all') {
            $query->whereHas('recurringJournals', function($q) use ($request) {
                $q->where('Frequency', $request->frequency);
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
        $recurringJournals = $query->paginate($perPage)->withQueryString();

        // Get filter options for dropdowns
        $frequencies = CodeDetail::where('CodeID', 'JournalPaymentFrequency')
            ->pluck('Description', 'Value')
            ->toArray();

        $approvalStatuses = FinanceJournalEntry::distinct()
            ->where('Type', 'recurring')
            ->pluck('ApprovalStatus')
            ->filter()
            ->unique()
            ->values();

        return view('finance.generalledger.recurrentjournal.index', compact(
            'recurringJournals',
            'frequencies',
            'approvalStatuses'
        ));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        try {
            $gls = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->get();
            $branches = Branch::select('Id', 'Name')->get();
            $departments = Department::select('Id', 'Name')->get();
            $paymentFrequency = CodeDetail::select('CodeID', 'Description', 'Value')->where('CodeID', 'JournalPaymentFrequency')->get();
            return view('finance.generalledger.recurrentjournal.create', compact('gls', 'branches', 'departments', 'paymentFrequency'));
        } catch (\Throwable) {
            Log::error('Error in Recurrent Journal Create');
            return back()->with('error', 'Error in Recurrent Journal Create');
        }
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceJournalEntry::class);
        // Normalize entries
        $entries = [];
        foreach ($request->GLAccount as $index => $gl) {
            $entries[] = [
                'gl_id' => $gl,
                'branch_id' => $request->Branch[$index],
                'department_id' => $request->Department[$index],
                'debit' => $request->DRCR[$index] === 'DR' ? $request->Amount[$index] : 0,
                'credit' => $request->DRCR[$index] === 'CR' ? $request->Amount[$index] : 0,
                'is_debit' => $request->DRCR[$index] === 'DR',
                'amount' => $request->Amount[$index],
                'narration' => $request->Narration[$index] ?? null,
            ];
        }

        $request->merge(['entries' => $entries]);

        // Validate input
        $validated = $request->validate([
            'StartDate' => 'required|date',
            'CuttOffDate' => 'required|date|after_or_equal:StartDate',
            'Frequency' => 'required|in:d,w,m,q,y',
            'ReferenceName' => 'required|string|max:255',
            'Description' => 'nullable|string|max:1000',
            'entries' => 'required|array|min:2',
            'entries.*.gl_id' => 'required|exists:t_FinanceGLAccounts,Id',
            'entries.*.branch_id' => 'required|exists:t_Branches,Id',
            'entries.*.department_id' => 'required|exists:t_Departments,Id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.credit' => 'required|numeric|min:0',
        ]);

        // Check if DR equals CR
        $totalDebit = collect($validated['entries'])->sum('debit');
        $totalCredit = collect($validated['entries'])->sum('credit');

        if ($totalDebit !== $totalCredit) {
            return back()->withErrors(['Amount mismatch' => 'Total Debit must equal Total Credit'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Save the master entry in the JournalEntry Table
            $journalEntry = FinanceJournalEntry::create([
                'Date' => $validated['StartDate'],
                'Description'    => $validated['Description'],
                'Type'=> 'recurring',
                'CreatedBy' => Auth::id(),
                'ModifiedBy'=> Auth::Id(),
            ]);
            //Saveto the Recurrent Journal Table
            $store=RecurrentJournal::create([
                'JournalEntryId'=> $journalEntry->Id,
                'StartDate'      => $validated['StartDate'],
                'CuttOffDate'    => $validated['CuttOffDate'],
                'Frequency'      => $validated['Frequency'],
                'ReferenceName'  => $validated['ReferenceName'],
                'Description'    => $validated['Description'],
                'CreatedBy'      => Auth::id(),
                'ModifiedBy'     => Auth::id(),
            ]);
            // SAVE to the JournalLines table
            foreach ($request->entries as $entry) {
                FinanceJournalLines::create([
                    'JournalEntryId' => $journalEntry->Id,
                    'GLAccountID'    => $entry['gl_id'],
                    'BranchID'       => $entry['branch_id'],
                    'DepartmentID'   => $entry['department_id'],
                    'IsDebit'        => $entry['is_debit'],
                    'Amount' => $entry['is_debit'] ? $entry['amount'] * -1 : $entry['amount'],
                    'Debit' => ($entry['debit'] * -1) ?? 0,
                    'Credit'         => $entry['credit'] ?? 0,
                    'Narration'      => $entry['narration'] ?? null,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy'=> Auth::Id(),
                ]);
            }
            activity('Recurring Journal Entry')
                ->performedOn(new FinanceJournalEntry())
                ->causedBy(Auth::id())
                ->withProperties(['Create' =>$store])
                ->log('Created Recurring Journal Entry');
            DB::commit();
            return back()->with('success', 'Recurrent Journal created successfully.');
        }catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error in Recurrent Journal Store');
            return back()->with('error', 'Error in Storing Recurrent Journal.');
        }
    }

    public function show($id){
        $this->authorize(PermissionEnum::FinanceGeneralLedgerView, ReverseJournalEntry::class);
        $journalEntry = FinanceJournalEntry::with([
            'recurringJournals',
            'journalLines.glAccount',
            'sourceModule',
            'createdBy:Id,Name',
            'modifiedBy:Id,Name',
            'reversalsAsOriginal' => function($query) {
                $query->with('journalEntry.createdBy:Id,Name');
            }
        ])->findOrFail($id);
        $frequencies =CodeDetail::where('CodeID', 'JournalPaymentFrequency')->pluck('Description', 'Value')->toArray();

        return view('finance.generalledger.recurrentjournal.show', compact('journalEntry','frequencies'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerUpdate, FinanceJournalEntry::class);
        $journalEntry = FinanceJournalEntry::with('journalLines', 'recurringJournals')->findOrFail($id);
        $gls = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->get();
        $branches = Branch::select('Id', 'Name')->get();
        $departments = Department::select('Id', 'Name')->get();
        $paymentFrequency = CodeDetail::select('CodeID', 'Description', 'Value')->where('CodeID', 'JournalPaymentFrequency')->get();
        return view('finance.generalledger.recurrentjournal.edit', compact('journalEntry', 'gls', 'branches', 'departments', 'paymentFrequency'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerUpdate, FinanceJournalEntry::class);
        $journalEntry = FinanceJournalEntry::with('journalLines', 'recurringJournals')->findOrFail($id);

        // Normalize entries with optional LineId[] for diff
        $entries = [];
        foreach ($request->GLAccount as $index => $gl) {
            $drcr = $request->DRCR[$index] ?? null;
            $amount = (float)($request->Amount[$index] ?? 0);
            $entries[] = [
                'line_id' => $request->LineId[$index] ?? null,
                'gl_id' => $gl,
                'branch_id' => $request->Branch[$index] ?? null,
                'department_id' => $request->Department[$index] ?? null,
                'debit' => $drcr === 'DR' ? $amount : 0,
                'credit' => $drcr === 'CR' ? $amount : 0,
                'is_debit' => $drcr === 'DR',
                'amount' => $amount,
                'narration' => $request->Narration[$index] ?? null,
            ];
        }

        $request->merge(['entries' => $entries]);
        $validated = $request->validate([
            'StartDate' => 'required|date',
            'CuttOffDate' => 'required|date|after_or_equal:StartDate',
            'Frequency' => 'required|in:d,w,m,q,y',
            'ReferenceName' => 'required|string|max:255',
            'Description' => 'nullable|string|max:1000',
            'entries' => 'required|array|min:2',
            'entries.*.line_id' => 'nullable|integer',
            'entries.*.gl_id' => 'required|exists:t_FinanceGLAccounts,Id',
            'entries.*.branch_id' => 'required|exists:t_Branches,Id',
            'entries.*.department_id' => 'required|exists:t_Departments,Id',
            'entries.*.debit' => 'required|numeric|min:0',
            'entries.*.credit' => 'required|numeric|min:0',
        ]);

        $totalDebit = collect($validated['entries'])->sum('debit');
        $totalCredit = collect($validated['entries'])->sum('credit');
        if ($totalDebit != $totalCredit) {
            return back()->withErrors(['Amount mismatch' => 'Total Debit must equal Total Credit'])->withInput();
        }

        // Validate ownership of incoming line_ids
        $existingIds = $journalEntry->journalLines->pluck('Id')->map(fn($v) => (int)$v)->all();
        $incomingIds = collect($validated['entries'])->pluck('line_id')->filter()->map(fn($v) => (int)$v)->all();
        foreach ($incomingIds as $lid) {
            if (!in_array($lid, $existingIds, true)) {
                return back()->withErrors(['Invalid line submitted' => 'One or more lines do not belong to this journal entry.'])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Update recurring header (in journal and recurrent table)
            $journalEntry->Date = $validated['StartDate'];
            $journalEntry->Description = $validated['Description'] ?? null;
            $journalEntry->ModifiedBy = Auth::id();
            $journalEntry->save();

            $rec = $journalEntry->recurringJournals->first();
            if ($rec) {
                $rec->StartDate = $validated['StartDate'];
                $rec->CuttOffDate = $validated['CuttOffDate'];
                $rec->Frequency = $validated['Frequency'];
                $rec->ReferenceName = $validated['ReferenceName'];
                $rec->Description = $validated['Description'] ?? null;
                $rec->ModifiedBy = Auth::id();
                $rec->save();
            }

            // Delete removed lines
            $toDelete = array_diff($existingIds, $incomingIds);
            if (!empty($toDelete)) {
                FinanceJournalLines::where('JournalEntryId', $journalEntry->Id)->whereIn('Id', $toDelete)->delete();
            }

            // Upsert
            foreach ($entries as $entry) {
                $payload = [
                    'GLAccountID' => $entry['gl_id'],
                    'BranchID' => $entry['branch_id'],
                    'DepartmentID' => $entry['department_id'],
                    'IsDebit' => (bool)$entry['is_debit'],
                    'Amount' => (bool)$entry['is_debit'] ? (float)$entry['amount'] * -1 : (float)$entry['amount'],
                    'Debit' => (float)($entry['debit'] * -1 ?? 0),
                    'Credit' => (float)($entry['credit'] ?? 0),
                    'Narration' => $entry['narration'] ?? null,
                    'ModifiedBy' => Auth::id(),
                ];
                if (!empty($entry['line_id'])) {
                    FinanceJournalLines::where('JournalEntryId', $journalEntry->Id)->where('Id', (int)$entry['line_id'])->update($payload);
                } else {
                    FinanceJournalLines::create(array_merge($payload, [
                        'JournalEntryId' => $journalEntry->Id,
                        'CreatedBy' => Auth::id(),
                    ]));
                }
            }

            activity('Recurring Journal Update')
                ->performedOn(new FinanceJournalEntry())
                ->causedBy(Auth::id())
                ->withProperties(['Update' => $journalEntry])
                ->log('Updated Recurring Journal');

            DB::commit();
            return redirect()->route('recurrentjournal.show', $journalEntry->Id)->with('success', 'Recurring Journal updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating Recurring Journal: ' . $th->getMessage());
            return back()->with('error', 'Failed to update Recurring Journal')->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerDelete, FinanceJournalEntry::class);
        $entry = FinanceJournalEntry::with('recurringJournals')->findOrFail($id);
        DB::transaction(function () use ($entry) {
            FinanceJournalLines::where('JournalEntryId', $entry->Id)->delete();
            \App\Models\Finance\RecurrentJournal::where('JournalEntryId', $entry->Id)->delete();
            $entry->delete();
        });
        return redirect()->route('recurrentjournal.index')->with('success', 'Recurring journal deleted.');
    }
}
