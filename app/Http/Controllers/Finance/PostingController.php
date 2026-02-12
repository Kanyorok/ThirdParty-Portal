<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\PermissionEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\ReverseJournalEntry;
use App\Services\Workflow\ApprovalWorkflow;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PostingController extends Controller
{
    protected $workflowService;

    public function __construct(ApprovalWorkflow $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Approve or reject a journal entry and post to the transaction table if approved.
     */
    public function journalApproval(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceTransaction::class);

        $validated = $request->validate([
            'action_type' => 'required|in:approve,reject,submitForApproval',
            'journalID' => 'required|integer|exists:t_FinanceJournalEntries,Id',
            'Reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $journal = FinanceJournalEntry::findOrFail($validated['journalID']);

            // TEMPORARY: workflow authorization is bypassed until workflow integration is completed
            // if (
            //     in_array($validated['action_type'], ['approve', 'reject'], true)
            //     && ! $this->workflowService->canApproveModel($journal, Auth::user())
            // ) {
            //     DB::rollBack();
            //
            //     return back()->with('fail', 'You are not authorized to approve this journal entry.');
            // }

            if ($validated['action_type'] === 'reject') {
                // TEMPORARY: bypass workflow reject and persist decision directly
                $journal->update([
                    'ApprovalStatus' => 'rejected',
                    'Status' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                ]);

                activity('Journal Entry Approval')
                    ->performedOn($journal)
                    ->causedBy(Auth::id())
                    ->withProperties(['action' => 'rejected', 'journal_id' => $journal->Id])
                    ->log('Rejected Journal Entry #' . $journal->RefNo);

                DB::commit();

                return back()->with('success', 'Journal Entry #' . $journal->RefNo . ' rejected successfully.');
            } elseif ($validated['action_type'] === 'submitForApproval') {
                $result = $this->submitForApproval($validated['journalID'], $validated['Reason']);
                if ($result) {
                    //Update Status Column to Pending
                    FinanceJournalEntry::where('Id', $validated['journalID'])->update(['Status' => 'pending']);
                    activity('Journal Entry Approval')
                        ->performedOn($journal)
                        ->causedBy(Auth::id())
                        ->withProperties(['action' => 'submittedForApproval', 'journal_id' => $journal->Id])
                        ->log('Submitted Journal Entry #' . $journal->RefNo . ' for approval.');
                    DB::commit();

                    return redirect()->back()->with('success', 'Journal Entry #' . $journal->RefNo . ' submitted for approval successfully.');
                } else {
                    $this->rollbackIfActive();

                    return redirect()->back()->with('error', 'Failed to submit journal entry for approval.');
                }
            } elseif ($validated['action_type'] === 'approve') {
                // TEMPORARY: workflow pending-approver checks are skipped

                // Proceed to posting
                $this->journalPosting($validated['journalID']);

                // Mark as posted after posting succeeds
                $journal->update([
                    'ApprovalStatus' => 'posted',
                    'Status' => 'posted',
                    'ApprovalReason' => $validated['Reason'],
                ]);

                activity('Journal Entry Approval')
                    ->performedOn($journal)
                    ->causedBy(Auth::id())
                    ->withProperties(['action' => 'approved', 'journal_id' => $journal->Id])
                    ->log('Approved Journal Entry #' . $journal->RefNo);

                // If this is a reversing journal, mark the original journal as reversed
                if (strtolower($journal->Type ?? '') === 'reversing') {
                    $rev = ReverseJournalEntry::where('JournalEntryId', $journal->Id)->first();
                    if ($rev) {
                        FinanceJournalEntry::where('Id', $rev->OriginalJournalEntryID)->update(['IsReversed' => true]);
                    }
                }
                DB::commit();

                return back()->with('success', 'Journal Entry #' . $journal->RefNo . ' approved and posted successfully.');
            }
        } catch (QueryException $e) {
            $this->rollbackIfActive();
            Log::error('Journal Approval Database Error: ' . $e->getMessage(), [
                'journalID' => $validated['journalID'],
                'action_type' => $validated['action_type'],
                'sql_error' => $e->getSql(),
            ]);

            return back()->with('error', 'Database Error: ' . $e->getMessage());
        } catch (\Throwable $th) {
            $this->rollbackIfActive();
            Log::error('Journal Approval Failed: ' . $th->getMessage(), [
                'journalID' => $validated['journalID'],
                'action_type' => $validated['action_type'],
            ]);

            return back()->with('error', 'Journal Approval Failed: ' . $th->getMessage());
        }
    }

    //Calling Approval Services Workflows
    public function submitForApproval($journalId, $remarks)
    {
        try {
            $journal = FinanceJournalEntry::findOrFail($journalId);

            try {
                $result = $this->workflowService->submit(
                    $journal,
                    Auth::user(),
                    ApprovalEnum::Submitted,
                    $remarks
                );
            } catch (ErroredException $e) {
                $message = strtolower($e->getMessage());
                if (str_contains($message, 'already submitted')) {
                    $journal->update([
                        'ApprovalStatus' => 'draft',
                        'ApprovalReason' => $remarks,
                    ]);

                    return true;
                }

                if (str_contains($message, 'invalid status')) {
                    Log::warning('Submit status missing; falling back to pending', [
                        'journal_id' => $journalId,
                    ]);

                    $result = $this->workflowService->submit(
                        $journal,
                        Auth::user(),
                        ApprovalEnum::Pending,
                        $remarks
                    );
                } else {
                    throw $e;
                }
            }

            if ($result) {
                // Update the document status for UI/state tracking
                $journal->update([
                    'ApprovalStatus' => 'draft',
                    'ApprovalReason' => $remarks,
                ]);
            }

            return (bool) $result;
        } catch (\Throwable $e) {
            Log::error('Journal submission failed', [
                'journal_id' => $journalId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function approve($journalId)
    {
        $journal = FinanceJournalEntry::findOrFail($journalId);
    }

    public function reject($journalId)
    {
        $journal = FinanceJournalEntry::findOrFail($journalId);
    }

    /**
     * Collect journal info and prepare data for posting to the transaction table.
     */
    public function journalPosting($journalId)
    {
        $journal = FinanceJournalEntry::with('journalLines')->findOrFail($journalId);
        $data = [];

        foreach ($journal->journalLines as $line) {
            // Prefer Amount if present; otherwise derive from Debit/Credit
            $derivedAmount = 0.0;
            if (isset($line->Amount) && $line->Amount !== null) {
                $derivedAmount = (float)$line->Amount;
            } else {
                $debit = (float)($line->Debit ?? 0);
                $credit = (float)($line->Credit ?? 0);
                $derivedAmount = $debit !== 0.0 ? $debit : $credit;
            }

            $rawAmount = abs((float)$derivedAmount);
            $isDebit = isset($line->IsDebit) ? (bool)$line->IsDebit : ((float)($line->Debit ?? 0) > 0);
            $amountToStore = $isDebit ? -$rawAmount : $rawAmount;
            $data[] = [
                'TransactionDate' => $journal->Date ? Carbon::parse($journal->Date) : Carbon::now(),
                'ReferenceNumber' => $journal->RefNo,
                'TransactionType' => 'Journal',
                'ModuleID' => 1100000,
                'SourceTable' => 't_FinanceJournalEntries',
                'GLAccountID' => $line->GLAccountID,
                'BranchID' => $line->BranchID ?? session('LoginBranchId', 1), // Fallback to 1 if unset
                'DepartmentID' => $line->DepartmentID,
                'DRCR' => $isDebit ? 'DR' : 'CR',
                'Amount' => $amountToStore,
                'CurrencyID' => 1,
                'CurrencyCode' => 'KES',
                'ExchangeRate' => 1,
                'Narration' => $line->Narration,
                'BatchNumber' => null,
                'IsTaxable' => false,
                'SystemDescription' => 'Journal Entry #' . $journal->RefNo,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ];
        }

        $this->postTransaction($data);
    }

    /**
     * Post transactions to the transaction table.
     */
    public function postTransaction(array $data)
    {
        $rules = [
            '*.TransactionDate' => 'required|date',
            '*.ReferenceNumber' => 'required|string|max:255',
            '*.TransactionType' => 'required|string|max:255',
            '*.ModuleID' => 'required|integer',
            '*.SourceTable' => 'nullable|string|max:255',
            '*.GLAccountID' => 'nullable|integer|exists:t_FinanceGLAccounts,Id',
            '*.BranchID' => 'required|integer|exists:t_Branches,Id',
            '*.DepartmentID' => 'nullable|integer|exists:t_Departments,Id',
            '*.Amount' => 'required|numeric',
//            '*.CurrencyID' => 'required|integer|exists:t_Currencies,Id',
//            '*.CurrencyCode' => 'required|string|max:3',
//            '*.ExchangeRate' => 'required|numeric|min:0',
//            '*.Narration' => 'nullable|string|max:255',
//            '*.BatchNumber' => 'nullable|string|max:255',
//            '*.IsTaxable' => 'required|boolean',
//            '*.SystemDescription' => 'nullable|string|max:255',
//            '*.CreatedBy' => 'required|integer|exists:t_Users,Id',
//            '*.ModifiedBy' => 'required|integer|exists:t_Users,Id',
//            '*.CreatedOn' => 'required|date',
//            '*.ModifiedOn' => 'required|date',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            Log::error('Transaction Validation Failed: ' . implode(', ', $errors), ['data' => $data]);

            throw new \RuntimeException('Transaction Validation Failed: ' . implode(', ', $errors));
        }

        foreach ($data as $index => $transaction) {
            try {
                $trx = FinanceTransaction::create($transaction);

                $this->updateBalanceForLine($trx->Id, $transaction);

                activity('Transaction Posting')
                    ->performedOn($trx)
                    ->causedBy(Auth::id())
                    ->withProperties(['transaction_id' => $trx->id, 'reference' => $transaction['ReferenceNumber']])
                    ->log('Posted Transaction #' . $transaction['ReferenceNumber']);
            } catch (QueryException $e) {
                Log::error('Transaction Posting Database Error at index ' . $index . ': ' . $e->getMessage(), [
                    'transaction' => $transaction,
                    'sql_error' => $e->getSql(),
                ]);

                throw new \RuntimeException('Failed to post transaction #' . ($index + 1) . ': ' . $e->getMessage(), 0, $e);
            }
        }
    }

    private function rollbackIfActive(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    }

    protected function updateBalanceForLine(int $trxId, array $trx): void
    {
        $glAccountId = (int)$trx['GLAccountID'];
        $branchId = $trx['BranchID'] ?? null;
        $amount = (float)$trx['Amount'];
        $drcr = strtoupper($trx['DRCR'] ?? 'DR');     // DR or CR
        $rate = (float)($trx['ExchangeRate'] ?? 1);
        $currencyId = $trx['CurrencyID'] ?? null;

        // Sign: DR = -, CR = +
        $signed = $drcr === 'DR' ? -$amount : $amount;

        // Base/ledger currency deltas (t_FinanceGLBranch stores all three)
        $localDelta = $signed * $rate;   // base/ledger currency delta
        $foreignDelta = $currencyId ? $signed : 0;

        // Format to avoid float noise in SQL
        $fmt = fn ($n) => number_format((float)$n, 5, '.', '');

        $uid = $trx['ModifiedBy'] ?? $trx['CreatedBy'] ?? (Auth::id() ?? 0);
        $now = now();

        // Grab GL meta (code + type). Prefer character "A/L/E/I/X" if available.
        $glMeta = DB::table('t_FinanceGLAccounts')
            ->select('GLCode', 'GLAccountTypeID')
            ->where('Id', $glAccountId)
            ->first();

        $glCode = $trx['GLCode'] ?? ($glMeta->GLCode ?? '');
        $glAccountType = $trx['GLAccountTypeID'] ?? ($glMeta->GLAccountType ?? ($glMeta->GLAccountTypeID ?? null));

        // Does the (GLAccountID, BranchID) row exist?
        $exists = DB::table('t_FinanceGLBranch')
            ->where('GLAccountID', $glAccountId)
            ->when(
                is_null($branchId),
                fn ($q) => $q->whereNull('BranchID'),
                fn ($q) => $q->where('BranchID', $branchId)
            )
            ->exists();

        if ($exists) {
            // UPDATE path → increment balances
            DB::table('t_FinanceGLBranch')
                ->when(true, function ($q) use ($glAccountId, $branchId) {
                    $q->where('GLAccountID', $glAccountId);

                    return is_null($branchId) ? $q->whereNull('BranchID') : $q->where('BranchID', $branchId);
                })
                ->update([
                    'LastTransactionId' => $trxId,
                    // increment with DB::raw – use base/ledger delta for Balance/LocalBalance
                    'Balance' => DB::raw('Balance + ' . $fmt($localDelta)),
                    'LocalBalance' => DB::raw('LocalBalance + ' . $fmt($localDelta)),
                    'ForeignBalance' => DB::raw('ForeignBalance + ' . $fmt($foreignDelta)),
                    'ModifiedOn' => $now,
                    'ModifiedBy' => $uid,
                ]);
        } else {
            // INSERT path → set starting balances (no arithmetic here)
            DB::table('t_FinanceGLBranch')->insert([
                'GLAccountID' => $glAccountId,
                'BranchID' => $branchId,
                'GLCode' => $glCode,
                'GLAccountType' => (string)$glAccountType,
                'LastTransactionId' => $trxId,
                'IsActive' => 1,
                'BankID' => $trx['BankID'] ?? null,

                // starting balances (base/ledger = localDelta)
                'Balance' => $fmt($localDelta),
                'LocalBalance' => $fmt($localDelta),
                'ForeignBalance' => $fmt($foreignDelta),

                // audit — set BOTH created & modified to satisfy NOT NULL constraints
                'CreatedOn' => $now,
                'CreatedBy' => $trx['CreatedBy'] ?? $uid,
                'ModifiedOn' => $now,
                'ModifiedBy' => $uid,
            ]);
        }
    }
}
