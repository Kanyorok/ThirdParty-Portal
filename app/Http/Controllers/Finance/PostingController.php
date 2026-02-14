<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\PermissionEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceSyncGLAccount;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\ReverseJournalEntry;
use App\Services\Workflow\ApprovalWorkflow;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

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
            $amountToStore = $isDebit ? -$rawAmount : $rawAmount;
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

        $this->postTransaction($data, $externalIdempotencyKey);
    }

    /**
     * Post transactions to the transaction table.
     */
    public function postTransaction(array $data, ?string $externalIdempotencyKey = null)
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

        $strictThirdPartyPosting = $this->isThirdPartyFinancePostingEnabled();
        $ownsTransaction = $strictThirdPartyPosting && DB::transactionLevel() === 0;

        if ($ownsTransaction) {
            DB::beginTransaction();
        }

        try {
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

            if ($strictThirdPartyPosting) {
                // Strict mode: third-party post must succeed, otherwise roll back.
                $this->postTransactionsToNimble($data, $externalIdempotencyKey);
            }

            if ($ownsTransaction) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if ($ownsTransaction && DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;
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

    private function isThirdPartyFinancePostingEnabled(): bool
    {
        return filter_var(
            env('ALLOW_THIRD_PARTY_FINANCE_POSTING', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private function postTransactionsToNimble(array $transactions, ?string $externalIdempotencyKey = null): void
    {
        $loginUrl = env('CRDB_NMB_LOGIN_URL');
        $baseUrl = rtrim((string) env('CRDB_NMB_URL'), '/');
        $consumerKey = env('CRDB_NMB_CONSUMER_KEY');
        $consumerSecret = env('CRDB_NMB_CONSUMER_SECRET');

        if (! $loginUrl || ! $baseUrl || ! $consumerKey || ! $consumerSecret) {
            throw new RuntimeException('Missing Nimble credentials in environment configuration.');
        }

        $payloads = $this->buildNimbleTransactionPayloads($transactions, $externalIdempotencyKey);
        if (empty($payloads)) {
            throw new RuntimeException('No valid DR/CR pairs found for third-party posting payload.');
        }

        $accessToken = $this->getNimbleAccessToken(
            loginUrl: (string) $loginUrl,
            consumerKey: (string) $consumerKey,
            consumerSecret: (string) $consumerSecret
        );

        $endpoint = $baseUrl . '/Transaction/PostERPTransaction';
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'token' => $accessToken,
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        foreach ($payloads as $index => $payload) {
            $payloadMeta = '[DR:' . ($payload['DrAccountID'] ?? '-')
                . ', CR:' . ($payload['CrAccountID'] ?? '-')
                . ', DrBr:' . ($payload['DrOurBranchID'] ?? '-')
                . ', CrBr:' . ($payload['CrOurBranchID'] ?? '-')
                . ', Amt:' . ($payload['TrxAmount'] ?? '-')
                . ', UQ:' . ($payload['UniqueERPTrxCode'] ?? '-') . ']';

            $response = Http::withoutVerifying()
                ->timeout(120)
                ->withHeaders($headers)
                ->post($endpoint, $payload);

            $body = $response->json();
            if (! is_array($body)) {
                if (! $response->successful()) {
                    Log::error('Nimble transaction HTTP failure', [
                        'item' => $index + 1,
                        'payload' => $payload,
                        'status' => $response->status(),
                        'raw_response' => $response->body(),
                    ]);

                    throw new RuntimeException(
                        'Nimble transaction post failed with HTTP ' . $response->status() . ' at item ' . ($index + 1) . ' ' . $payloadMeta . '.'
                    );
                }

                throw new RuntimeException(
                    'Nimble transaction post returned invalid JSON payload at item ' . ($index + 1) . ' ' . $payloadMeta . '.'
                );
            }

            $message = (string) Arr::get($body, 'Message', 'Unknown Nimble error.');
            $status = strtoupper((string) Arr::get($body, 'Status', ''));
            $webServiceStatus = strtoupper((string) Arr::get($body, 'WebServiceStatus', ''));
            $responseCode = (int) Arr::get($body, 'Code', 0);
            $isSuccess = Arr::get($body, 'IsSuccess');
            $legacySuccess = Arr::get($body, 'Success');

            if (! $response->successful()) {
                Log::error('Nimble transaction rejected with HTTP error', [
                    'item' => $index + 1,
                    'payload' => $payload,
                    'status' => $response->status(),
                    'response' => $body,
                ]);

                throw new RuntimeException(
                    'Nimble transaction post failed at item ' . ($index + 1)
                    . ' ' . $payloadMeta . ': '
                    . $message
                );
            }

            $successFlag = null;
            if ($isSuccess !== null) {
                $successFlag = (bool) $isSuccess;
            } elseif ($legacySuccess !== null) {
                $successFlag = (bool) $legacySuccess;
            }

            $statusOk = $status === '' || in_array($status, ['OK', 'SUCCESS', '00', '000'], true);
            $webStatusOk = $webServiceStatus === '' || in_array($webServiceStatus, ['00', '000', 'OK'], true);
            $codeOk = $responseCode === 0 || $responseCode === 200;
            $payloadOk = ($successFlag === true)
                || ($successFlag === null && $statusOk && $webStatusOk && $codeOk);

            if (! $payloadOk) {
                Log::warning('Nimble transaction business rejection', [
                    'item' => $index + 1,
                    'payload' => $payload,
                    'response' => $body,
                ]);

                throw new RuntimeException(
                    'Nimble transaction rejected at item ' . ($index + 1)
                    . ' ' . $payloadMeta . ': '
                    . $message
                );
            }
        }
    }

    private function buildNimbleTransactionPayloads(array $transactions, ?string $externalIdempotencyKey = null): array
    {
        if (empty($transactions)) {
            return [];
        }

        $glAccountIds = collect($transactions)
            ->pluck('GLAccountID')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $branchIds = collect($transactions)
            ->pluck('BranchID')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $glAccounts = FinanceGLAccounts::query()
            ->whereIn('Id', $glAccountIds)
            ->select('Id', 'GLCode', 'GLName', 'MappedGLCode')
            ->get()
            ->keyBy('Id');

        $mappedCodes = $glAccounts
            ->pluck('MappedGLCode')
            ->filter(fn ($code) => trim((string) $code) !== '')
            ->map(fn ($code) => trim((string) $code))
            ->unique()
            ->values();

        $nimbleAccounts = FinanceSyncGLAccount::query()
            ->whereIn('GLCode', $mappedCodes)
            ->select('GLCode', 'GLName', 'IsActive', 'IsPostingAccount', 'SourceTable')
            ->get()
            ->keyBy('GLCode');

        $branchCodes = Branch::query()
            ->whereIn('Id', $branchIds)
            ->pluck('BranchID', 'Id');

        $debits = [];
        $credits = [];

        foreach ($transactions as $transaction) {
            $amount = abs((float) ($transaction['Amount'] ?? 0));
            if ($amount <= 0) {
                continue;
            }

            $glAccountId = (int) ($transaction['GLAccountID'] ?? 0);
            $gl = $glAccounts->get($glAccountId);
            if (! $gl) {
                throw new RuntimeException('Unable to resolve GL account for third-party posting. GL ID: ' . $glAccountId);
            }

            $mappedGlCode = trim((string) ($gl->MappedGLCode ?? ''));
            if ($mappedGlCode === '') {
                throw new RuntimeException('Missing MappedGLCode for GL account "' . ($gl->GLCode ?? $glAccountId) . '".');
            }

            $nimbleGl = $nimbleAccounts->get($mappedGlCode);
            if (! $nimbleGl) {
                throw new RuntimeException(
                    'Mapped Nimble GL "' . $mappedGlCode . '" for ERP GL "' . ($gl->GLCode ?? $glAccountId) . '" is not found in synced GL list.'
                );
            }

            if ((int) ($nimbleGl->IsActive ?? 1) !== 1) {
                throw new RuntimeException(
                    'Mapped Nimble GL "' . $mappedGlCode . '" for ERP GL "' . ($gl->GLCode ?? $glAccountId) . '" is inactive.'
                );
            }

            if ((int) ($nimbleGl->IsPostingAccount ?? 1) !== 1) {
                throw new RuntimeException(
                    'Mapped Nimble GL "' . $mappedGlCode . '" for ERP GL "' . ($gl->GLCode ?? $glAccountId) . '" is not a posting account.'
                );
            }

            if (stripos((string) ($nimbleGl->SourceTable ?? ''), 'GLInterface') !== false) {
                throw new RuntimeException(
                    'Mapped Nimble GL "' . $mappedGlCode . '" for ERP GL "' . ($gl->GLCode ?? $glAccountId) . '" is attached to GL Interface and cannot be posted directly.'
                );
            }

            $branchPk = (int) ($transaction['BranchID'] ?? 0);
            $branchCode = trim((string) ($branchCodes[$branchPk] ?? ''));

            if ($branchCode === '' || $branchCode === '000') {
                throw new RuntimeException(
                    'Invalid Nimble branch mapping for ERP branch ID ' . $branchPk
                    . ' (resolved code: ' . ($branchCode === '' ? 'empty' : $branchCode) . '). '
                    . 'Please update t_Branches.BranchID with a valid Nimble branch code (e.g. 001/002, not 000).'
                );
            }

            $line = [
                'remaining' => $amount,
                'account' => $mappedGlCode,
                'branch' => $branchCode,
                'description' => trim((string) ($transaction['Narration'] ?? $transaction['SystemDescription'] ?? '')),
                'reference' => trim((string) ($transaction['ReferenceNumber'] ?? 'ERPTRX')),
                'idempotency_key' => trim((string) ($transaction['IdempotencyKey'] ?? $externalIdempotencyKey ?? '')),
            ];

            $drcr = strtoupper((string) ($transaction['DRCR'] ?? ''));
            if ($drcr === 'DR') {
                $debits[] = $line;
            } elseif ($drcr === 'CR') {
                $credits[] = $line;
            }
        }

        if (empty($debits) || empty($credits)) {
            return [];
        }

        $payloads = [];
        $debitIndex = 0;
        $creditIndex = 0;
        $pairNo = 1;

        while ($debitIndex < count($debits) && $creditIndex < count($credits)) {
            $debit = &$debits[$debitIndex];
            $credit = &$credits[$creditIndex];

            $pairAmount = min((float) $debit['remaining'], (float) $credit['remaining']);
            if ($pairAmount <= 0) {
                if ((float) $debit['remaining'] <= 0) {
                    $debitIndex++;
                }
                if ((float) $credit['remaining'] <= 0) {
                    $creditIndex++;
                }
                continue;
            }

            $reference = $debit['reference'] !== '' ? $debit['reference'] : $credit['reference'];
            $description = $debit['description'] !== '' ? $debit['description'] : $credit['description'];
            if ($description === '') {
                $description = 'Journal Entry ' . $reference;
            }
            $idempotencyKey = $debit['idempotency_key'] !== ''
                ? $debit['idempotency_key']
                : $credit['idempotency_key'];

            $payloads[] = [
                'DrOurBranchID' => $debit['branch'],
                'CrOurBranchID' => $credit['branch'],
                'DrAccountID' => $debit['account'],
                'CrAccountID' => $credit['account'],
                'TrxAmount' => round($pairAmount, 2),
                'TrxDescription' => $description,
                'UniqueERPTrxCode' => $this->buildUniqueErpTransactionCode($idempotencyKey, $reference, $pairNo),
            ];

            $debit['remaining'] = (float) $debit['remaining'] - $pairAmount;
            $credit['remaining'] = (float) $credit['remaining'] - $pairAmount;

            if ((float) $debit['remaining'] <= 0.00001) {
                $debitIndex++;
            }
            if ((float) $credit['remaining'] <= 0.00001) {
                $creditIndex++;
            }
            $pairNo++;
        }

        $hasRemainderDebit = collect($debits)->contains(fn ($line) => (float) ($line['remaining'] ?? 0) > 0.00001);
        $hasRemainderCredit = collect($credits)->contains(fn ($line) => (float) ($line['remaining'] ?? 0) > 0.00001);
        if ($hasRemainderDebit || $hasRemainderCredit) {
            throw new RuntimeException('Unable to fully pair debit and credit lines for third-party posting.');
        }

        return $payloads;
    }

    private function buildUniqueErpTransactionCode(?string $idempotencyKey, string $reference, int $pairNo): string
    {
        $seed = trim((string) $idempotencyKey);
        if ($seed === '') {
            $seed = trim((string) $reference);
        }
        if ($seed === '') {
            $seed = (string) Str::uuid();
        }

        // If the source is already UUID, keep it for first pair and derive UUID-like codes for next pairs.
        if (Str::isUuid($seed) && $pairNo <= 1) {
            return strtolower($seed);
        }

        $hash = md5(strtolower($seed) . ':' . $pairNo);

        return strtolower(vsprintf('%s-%s-%s-%s-%s', [
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12),
        ]));
    }

    private function getNimbleAccessToken(string $loginUrl, string $consumerKey, string $consumerSecret): string
    {
        $response = Http::withoutVerifying()
            ->timeout(60)
            ->asJson()
            ->post($loginUrl, [
                'ConsumerKey' => $consumerKey,
                'ConsumerSecret' => $consumerSecret,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Nimble login failed with HTTP ' . $response->status() . '.');
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new RuntimeException('Nimble login returned invalid JSON payload.');
        }

        $accessToken = Arr::get($body, 'AccessToken')
            ?? Arr::get($body, 'accessToken')
            ?? Arr::get($body, 'access_token')
            ?? Arr::get($body, 'token');

        $statusCode = strtoupper((string) Arr::get($body, 'StatusCode', ''));
        $success = Arr::get($body, 'Success', true);
        $statusOk = in_array($statusCode, ['000', '00', 'OK'], true);

        if (! $accessToken || ! $success || ! $statusOk) {
            throw new RuntimeException('Nimble login did not return a valid access token.');
        }

        return (string) $accessToken;
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
