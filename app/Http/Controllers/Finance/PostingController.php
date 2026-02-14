<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\PermissionEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\ReverseJournalEntry;
use App\Services\Finance\TransactionService;
use App\Services\Workflow\ApprovalWorkflow;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PostingController extends Controller
{
    protected $workflowService;
    protected $transactionService;

    public function __construct(ApprovalWorkflow $workflowService, TransactionService $transactionService)
    {
        $this->workflowService = $workflowService;
        $this->transactionService = $transactionService;
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

            // TEMPORARY: workflow authorization is bypassed until workflow integration is completed.

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

            return back()
                ->with('error', 'Database Error: ' . $e->getMessage())
                ->with('page_error', 'Journal approval failed due to a database error. Please try again or contact support.');
        } catch (\Throwable $th) {
            $this->rollbackIfActive();
            Log::error('Journal Approval Failed: ' . $th->getMessage(), [
                'journalID' => $validated['journalID'],
                'action_type' => $validated['action_type'],
            ]);

            $message = 'Journal Approval Failed: ' . $th->getMessage();
            $pageError = $this->buildPersistentJournalErrorMessage($th->getMessage());

            return back()
                ->with('error', $message)
                ->with('page_error', $pageError);
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
        $externalIdempotencyKey = trim((string) ($journal->IdempotencyKey ?? ''));
        if ($externalIdempotencyKey === '') {
            $externalIdempotencyKey = (string) Str::uuid();
        }

        foreach ($journal->journalLines as $line) {
            if (empty($line->BranchID)) {
                throw new RuntimeException('Journal line is missing BranchID. Please set branch on all lines before approval.');
            }

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
            $data[] = [
                'TransactionDate' => $journal->Date ? Carbon::parse($journal->Date) : Carbon::now(),
                'ReferenceNumber' => $journal->RefNo,
                'TransactionType' => 'Journal',
                'ModuleID' => 1100000,
                'SourceTable' => 't_FinanceJournalEntries',
                'GLAccountID' => $line->GLAccountID,
                'BranchID' => $line->BranchID,
                'DepartmentID' => $line->DepartmentID,
                'DRCR' => $isDebit ? 'DR' : 'CR',
                'Amount' => $rawAmount,
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

        $this->postTransaction($data, $externalIdempotencyKey);
    }

    /**
     * Post transactions to the transaction table.
     */
    public function postTransaction(array $data, ?string $externalIdempotencyKey = null)
    {
        $this->transactionService->postTransactionLines($data, [
            'sync_third_party' => true,
            'external_idempotency_key' => $externalIdempotencyKey,
        ]);
    }

    private function rollbackIfActive(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    }

    private function buildPersistentJournalErrorMessage(string $rawMessage): string
    {
        if (stripos($rawMessage, 'Nimble transaction') !== false) {
            return 'Third-party posting to Nimble failed: ' . $rawMessage
                . '. The journal was not posted in ERP because the whole transaction was rolled back.';
        }

        return $rawMessage;
    }
}
