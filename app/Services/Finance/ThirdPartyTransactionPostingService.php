<?php

namespace App\Services\Finance;

use App\Models\Core\Branch;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceSyncGLAccount;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ThirdPartyTransactionPostingService
{
    public function isEnabled(): bool
    {
        return filter_var(
            env('ALLOW_THIRD_PARTY_FINANCE_POSTING', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function collectValidationIssues(array $transactions): array
    {
        if (! $this->isEnabled() || empty($transactions)) {
            return [];
        }

        [$glAccounts, $nimbleAccounts, $branchCodes] = $this->resolvePostingContext($transactions);
        $issues = [];

        foreach ($transactions as $index => $transaction) {
            $lineNo = $index + 1;
            $amount = abs((float) ($transaction['Amount'] ?? 0));
            if ($amount <= 0) {
                continue;
            }

            $drcr = strtoupper((string) ($transaction['DRCR'] ?? ''));
            if (! in_array($drcr, ['DR', 'CR'], true)) {
                $issues[] = "Line {$lineNo}: DR/CR indicator is missing.";

                continue;
            }

            $glAccountId = (int) ($transaction['GLAccountID'] ?? 0);
            $gl = $glAccounts->get($glAccountId);
            $erpGlCode = $gl?->GLCode ?? ('ID ' . $glAccountId);

            if (! $gl) {
                $issues[] = "Line {$lineNo} ({$erpGlCode}): ERP GL account is missing.";

                continue;
            }

            $mappedGlCode = trim((string) ($gl->MappedGLCode ?? ''));
            if ($mappedGlCode === '') {
                $issues[] = "Line {$lineNo} ({$erpGlCode}): Mapped Nimble GL is missing.";

                continue;
            }

            $nimbleGl = $nimbleAccounts->get($mappedGlCode);
            if (! $nimbleGl) {
                $issues[] = "Line {$lineNo} ({$erpGlCode}): Mapped Nimble GL {$mappedGlCode} was not found in synced GL list.";

                continue;
            }

            if ((int) ($nimbleGl->IsActive ?? 1) !== 1) {
                $issues[] = "Line {$lineNo} ({$erpGlCode}): Mapped Nimble GL {$mappedGlCode} is inactive.";
            }

            if ((int) ($nimbleGl->IsPostingAccount ?? 1) !== 1) {
                $issues[] = "Line {$lineNo} ({$erpGlCode}): Mapped Nimble GL {$mappedGlCode} is not a posting GL.";
            }

            if (stripos((string) ($nimbleGl->SourceTable ?? ''), 'GLInterface') !== false) {
                $issues[] = "Line {$lineNo} ({$erpGlCode}): Mapped Nimble GL {$mappedGlCode} is attached to GL Interface and may reject posting.";
            }

            $branchPk = (int) ($transaction['BranchID'] ?? 0);
            $branchCode = trim((string) ($branchCodes[$branchPk] ?? ''));
            if ($branchCode === '' || $branchCode === '000') {
                $resolved = $branchCode === '' ? 'empty' : $branchCode;
                $issues[] = "Line {$lineNo} ({$erpGlCode}): Branch mapping is invalid (resolved as {$resolved}).";
            }
        }

        return array_values(array_unique($issues));
    }

    public function postTransactions(array $transactions, ?string $externalIdempotencyKey = null): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $issues = $this->collectValidationIssues($transactions);
        if (! empty($issues)) {
            throw new RuntimeException($issues[0]);
        }

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
        [$glAccounts, $nimbleAccounts, $branchCodes] = $this->resolvePostingContext($transactions);

        $debits = [];
        $credits = [];

        foreach ($transactions as $transaction) {
            $amount = abs((float) ($transaction['Amount'] ?? 0));
            if ($amount <= 0) {
                continue;
            }

            $drcr = strtoupper((string) ($transaction['DRCR'] ?? ''));
            if (! in_array($drcr, ['DR', 'CR'], true)) {
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

            $branchPk = (int) ($transaction['BranchID'] ?? 0);
            $branchCode = trim((string) ($branchCodes[$branchPk] ?? ''));
            if ($branchCode === '' || $branchCode === '000') {
                $resolved = $branchCode === '' ? 'empty' : $branchCode;

                throw new RuntimeException(
                    'Invalid Nimble branch mapping for ERP branch ID ' . $branchPk
                    . ' (resolved code: ' . $resolved . '). '
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

            if ($drcr === 'DR') {
                $debits[] = $line;
            } else {
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

    private function resolvePostingContext(array $transactions): array
    {
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

        return [$glAccounts, $nimbleAccounts, $branchCodes];
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
}
