<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class PostingController extends Controller
{
    /**
     * Approve or reject a journal entry and post to the transaction table if approved.
     */
    public function journalApproval(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceGeneralLedgerCreate, FinanceTransaction::class);

        $validated = $request->validate([
            'action_type' => 'required|in:approve,reject',
            'journalID' => 'required|integer|exists:t_FinanceJournalEntries,Id',
            'Reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $journal = FinanceJournalEntry::findOrFail($validated['journalID']);

            if ($validated['action_type'] === 'reject') {
                $journal->update([
                    'ApprovalStatus' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                ]);

                activity('Journal Entry Approval')
                    ->performedOn($journal)
                    ->causedBy(Auth::id())
                    ->withProperties(['action' => 'rejected', 'journal_id' => $journal->Id])
                    ->log('Rejected Journal Entry #' . $journal->RefNo);

                DB::commit();
                return back()->with('success', 'Journal Entry #' . $journal->RefNo . ' rejected successfully.');
            } elseif ($validated['action_type'] === 'approve') {
                $journal->update([
                    'ApprovalStatus' => 'posted',
                    'ApprovalReason' => $validated['Reason'],
                ]);

                activity('Journal Entry Approval')
                    ->performedOn($journal)
                    ->causedBy(Auth::id())
                    ->withProperties(['action' => 'approved', 'journal_id' => $journal->Id])
                    ->log('Approved Journal Entry #' . $journal->RefNo);

                // Proceed to posting
                $result = $this->journalPosting($validated['journalID']);
                DB::commit();
                return $result;
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Journal Approval Database Error: ' . $e->getMessage(), [
                'journalID' => $validated['journalID'],
                'action_type' => $validated['action_type'],
                'sql_error' => $e->getSql(),
            ]);
            return back()->with('error', 'Database Error: ' . $e->getMessage());
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Journal Approval Failed: ' . $th->getMessage(), [
                'journalID' => $validated['journalID'],
                'action_type' => $validated['action_type'],
            ]);
            return back()->with('error', 'Journal Approval Failed: ' . $th->getMessage());
        }
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

        return $this->postTransaction($data);
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
            return back()->withErrors($validator)->withInput();
        }

        try {
            foreach ($data as $index => $transaction) {
                try {
                    $trx = FinanceTransaction::create($transaction);

                    $this->updateBalanceForLine($trx->Id,$transaction);

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
                    throw new \Exception('Failed to post transaction #' . ($index + 1) . ': ' . $e->getMessage());
                }
            }

            return back()->with('success', 'Transactions posted successfully.');
        } catch (\Throwable $th) {
            Log::error('Transaction Posting Failed: ' . $th->getMessage(), [
                'data' => $data,
                'trace' => $th->getTraceAsString(),
            ]);
            return back()->with('error', 'Transaction Posting Failed: ' . $th->getMessage());
        }
    }

    protected function updateBalanceForLine(int $trxId, array $trx): void
    {
        $glAccountId = (int)$trx['GLAccountID'];
        $branchId    = $trx['BranchID'] ?? null;
        $amount      = (float)$trx['Amount'];
        $drcr        = strtoupper($trx['DRCR'] ?? 'DR');     // DR or CR
        $rate        = (float)($trx['ExchangeRate'] ?? 1);
        $currencyId  = $trx['CurrencyID'] ?? null;

        // Sign: DR = -, CR = +
        $signed       = $drcr === 'DR' ? -$amount : $amount;

        // Base/ledger currency deltas (t_FinanceGLBranch stores all three)
        $localDelta   = $signed * $rate;   // base/ledger currency delta
        $foreignDelta = $currencyId ? $signed : 0;

        // Format to avoid float noise in SQL
        $fmt = fn($n) => number_format((float)$n, 5, '.', '');

        $uid = $trx['ModifiedBy'] ?? $trx['CreatedBy'] ?? (Auth::id() ?? 0);
        $now = now();

        // Grab GL meta (code + type). Prefer character "A/L/E/I/X" if available.
        $glMeta = DB::table('t_FinanceGLAccounts')
            ->select('GLCode', 'GLAccountTypeID')
            ->where('Id', $glAccountId)
            ->first();

        $glCode        = $trx['GLCode']        ?? ($glMeta->GLCode ?? '');
        $glAccountType = $trx['GLAccountTypeID'] ?? ($glMeta->GLAccountType ?? ($glMeta->GLAccountTypeID ?? null));

        // Does the (GLAccountID, BranchID) row exist?
        $exists = DB::table('t_FinanceGLBranch')
            ->where('GLAccountID', $glAccountId)
            ->when(is_null($branchId), fn($q) => $q->whereNull('BranchID'),
                fn($q) => $q->where('BranchID', $branchId))
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
                    'Balance'           => DB::raw('Balance + '      . $fmt($localDelta)),
                    'LocalBalance'      => DB::raw('LocalBalance + ' . $fmt($localDelta)),
                    'ForeignBalance'    => DB::raw('ForeignBalance + ' . $fmt($foreignDelta)),
                    'ModifiedOn'        => $now,
                    'ModifiedBy'        => $uid,
                ]);

        } else {
            // INSERT path → set starting balances (no arithmetic here)
            DB::table('t_FinanceGLBranch')->insert([
                'GLAccountID'       => $glAccountId,
                'BranchID'          => $branchId,
                'GLCode'            => $glCode,
                'GLAccountType'     => (string)$glAccountType,
                'LastTransactionId' => $trxId,
                'IsActive'          => 1,
                'BankID'            => $trx['BankID'] ?? null,

                // starting balances (base/ledger = localDelta)
                'Balance'           => $fmt($localDelta),
                'LocalBalance'      => $fmt($localDelta),
                'ForeignBalance'    => $fmt($foreignDelta),

                // audit — set BOTH created & modified to satisfy NOT NULL constraints
                'CreatedOn'         => $now,
                'CreatedBy'         => $trx['CreatedBy'] ?? $uid,
                'ModifiedOn'        => $now,
                'ModifiedBy'        => $uid,
            ]);
        }
    }
}
