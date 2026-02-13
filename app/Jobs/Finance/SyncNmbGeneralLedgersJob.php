<?php

namespace App\Jobs\Finance;

use App\Models\Finance\FinanceGLSyncRun;
use App\Models\Finance\FinanceSyncGLAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SyncNmbGeneralLedgersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;

    public function __construct(public int $syncRunId)
    {
    }

    public function handle(): void
    {
        $syncRun = FinanceGLSyncRun::find($this->syncRunId);
        if (! $syncRun) {
            return;
        }

        $userId = $syncRun->CreatedBy ?? 1;

        try {
            $syncRun->update([
                'Status' => 'running',
                'Message' => 'Starting Nimble GL sync (staging mode).',
                'SyncError' => null,
                'StartedAt' => $syncRun->StartedAt ?? now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);

            $loginUrl = env('CRDB_NMB_LOGIN_URL');
            $baseUrl = rtrim((string) env('CRDB_NMB_URL'), '/');
            $bankId = (string) env('CRDB_NMB_BANKID', env('CRDB_NMB_BANK_ID', '00'));
            $consumerKey = env('CRDB_NMB_CONSUMER_KEY');
            $consumerSecret = env('CRDB_NMB_CONSUMER_SECRET');

            if (! $loginUrl || ! $baseUrl || ! $consumerKey || ! $consumerSecret || ! $bankId) {
                throw new RuntimeException('Missing Nimble credentials in environment configuration.');
            }

            $accessToken = $this->getNmbAccessToken($loginUrl, $consumerKey, $consumerSecret);
            $endpoint = $baseUrl . '/GeneralLedger/SyncGeneralLedgers';

            $pageSize = max(1, min(5000, (int) ($syncRun->PageSize ?: 1000)));
            $nextCursor = null;
            $nextPageUrl = null;
            $page = 0;
            $recordsSynced = 0;
            $totalRecords = null;
            $seenCursors = [];

            DB::table('t_FinanceSyncGLAccountsStaging')->truncate();

            while (true) {
                $page++;

                $response = $this->fetchGeneralLedgersPage(
                    endpoint: $endpoint,
                    accessToken: $accessToken,
                    bankId: $bankId,
                    pageSize: $pageSize,
                    nextCursor: $nextCursor,
                    nextPageUrl: $nextPageUrl
                );

                if (! $response->successful()) {
                    throw new RuntimeException('Nimble GL sync API failed with HTTP ' . $response->status() . '.');
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    throw new RuntimeException('Nimble GL sync API returned invalid JSON payload.');
                }

                $status = strtoupper((string) Arr::get($payload, 'Status', ''));
                $code = (int) Arr::get($payload, 'Code', 0);
                if (! in_array($status, ['00', '000', 'OK'], true) || $code !== 200) {
                    throw new RuntimeException('Nimble GL sync API returned non-success status payload.');
                }

                $rows = Arr::get($payload, 'Data', []);
                if (! is_array($rows)) {
                    $rows = [];
                }

                $now = now();
                $preparedRows = collect($rows)
                    ->map(fn ($row) => $this->mapLedgerRow(is_array($row) ? $row : (array) $row, $userId, $now))
                    ->filter()
                    ->unique('GLCode')
                    ->values()
                    ->all();

                if (! empty($preparedRows)) {
                    // SQL Server has a strict parameter limit; write in smaller batches.
                    foreach (array_chunk($preparedRows, 50) as $batch) {
                        DB::table('t_FinanceSyncGLAccountsStaging')->upsert(
                            $batch,
                            ['GLCode'],
                            [
                                'GLName',
                                'GLAccountTypeID',
                                'GLTypeGroupID',
                                'GLSubAccountTypeID',
                                'ParentGLID',
                                'NormalBalance',
                                'IsControlAccount',
                                'IsPostingAccount',
                                'CBSAccountCode',
                                'BranchID',
                                'GLAccountTypeValue',
                                'GLTypeGroupIDValue',
                                'GLTypeGroupValue',
                                'GLSubAccountTypeIDValue',
                                'GLDigits',
                                'Description',
                                'IsActive',
                                'CurrencyID',
                                'Source',
                                'SourceTable',
                                'IsSynced',
                                'ModifiedBy',
                                'ModifiedOn',
                            ]
                        );
                    }
                }

                $recordsSynced += count($preparedRows);
                $totalRecordsValue = Arr::get($payload, 'TotalRecords');
                $totalRecords = is_numeric($totalRecordsValue) ? (int) $totalRecordsValue : $totalRecords;

                $nextCursorValue = trim((string) Arr::get($payload, 'NextCursor', ''));
                $nextPageUrlValue = trim((string) Arr::get($payload, 'NextPageUrl', ''));

                $syncRun->update([
                    'Status' => 'running',
                    'Message' => 'Staged page ' . $page . '.',
                    'RecordsSynced' => $recordsSynced,
                    'TotalRecords' => $totalRecords,
                    'CurrentPage' => $page,
                    'PageSize' => $pageSize,
                    'LastCursor' => $nextCursorValue !== '' ? $nextCursorValue : null,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);

                if ($nextCursorValue === '' && $nextPageUrlValue === '') {
                    break;
                }

                if ($nextCursorValue !== '') {
                    if (isset($seenCursors[$nextCursorValue])) {
                        throw new RuntimeException('Pagination loop detected. Cursor repeated: ' . $nextCursorValue);
                    }
                    $seenCursors[$nextCursorValue] = true;
                }

                if (empty($rows)) {
                    break;
                }

                if ($totalRecords !== null && $recordsSynced >= $totalRecords) {
                    break;
                }

                $nextCursor = $nextCursorValue !== '' ? $nextCursorValue : null;
                $nextPageUrl = $nextPageUrlValue !== '' ? $nextPageUrlValue : null;
            }

            $syncRun->update([
                'Status' => 'running',
                'Message' => 'Applying staged snapshot.',
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);

            DB::transaction(function () {
                FinanceSyncGLAccount::truncate();

                DB::table('t_FinanceSyncGLAccounts')->insertUsing(
                    [
                        'GLCode',
                        'GLName',
                        'GLAccountTypeID',
                        'GLTypeGroupID',
                        'GLSubAccountTypeID',
                        'ParentGLID',
                        'NormalBalance',
                        'IsControlAccount',
                        'IsPostingAccount',
                        'CBSAccountCode',
                        'BranchID',
                        'GLAccountTypeValue',
                        'GLTypeGroupIDValue',
                        'GLTypeGroupValue',
                        'GLSubAccountTypeIDValue',
                        'GLDigits',
                        'Description',
                        'IsActive',
                        'CurrencyID',
                        'Source',
                        'SourceTable',
                        'IsSynced',
                        'CreatedBy',
                        'CreatedOn',
                        'ModifiedBy',
                        'ModifiedOn',
                        'DeletedBy',
                        'DeletedOn',
                    ],
                    DB::table('t_FinanceSyncGLAccountsStaging')->select(
                        'GLCode',
                        'GLName',
                        'GLAccountTypeID',
                        'GLTypeGroupID',
                        'GLSubAccountTypeID',
                        'ParentGLID',
                        'NormalBalance',
                        'IsControlAccount',
                        'IsPostingAccount',
                        'CBSAccountCode',
                        'BranchID',
                        'GLAccountTypeValue',
                        'GLTypeGroupIDValue',
                        'GLTypeGroupValue',
                        'GLSubAccountTypeIDValue',
                        'GLDigits',
                        'Description',
                        'IsActive',
                        'CurrencyID',
                        'Source',
                        'SourceTable',
                        'IsSynced',
                        'CreatedBy',
                        'CreatedOn',
                        'ModifiedBy',
                        'ModifiedOn',
                        'DeletedBy',
                        'DeletedOn'
                    )
                );
            });

            $appliedCount = FinanceSyncGLAccount::count();

            DB::table('t_FinanceSyncGLAccountsStaging')->truncate();

            $syncRun->update([
                'Status' => 'completed',
                'Message' => 'Nimble GL sync completed successfully. Snapshot applied.',
                'CompletedAt' => now(),
                'RecordsSynced' => $appliedCount,
                'TotalRecords' => $totalRecords ?? $recordsSynced,
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Nimble GL sync job failed', [
                'syncRunId' => $this->syncRunId,
                'message' => $e->getMessage(),
            ]);

            $syncRun->update([
                'Status' => 'failed',
                'Message' => 'Nimble GL sync failed: ' . mb_substr($e->getMessage(), 0, 300),
                'SyncError' => $e->getMessage(),
                'CompletedAt' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
        }
    }

    private function getNmbAccessToken(string $loginUrl, string $consumerKey, string $consumerSecret): string
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

    private function fetchGeneralLedgersPage(
        string $endpoint,
        string $accessToken,
        string $bankId,
        int $pageSize,
        ?string $nextCursor,
        ?string $nextPageUrl
    ): \Illuminate\Http\Client\Response {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'token' => $accessToken,
        ];

        if ($nextPageUrl) {
            $getResponse = Http::withoutVerifying()
                ->timeout(180)
                ->withHeaders($headers)
                ->get($nextPageUrl);

            if ($getResponse->successful()) {
                return $getResponse;
            }
        }

        $payload = [
            'BankID' => $bankId,
            'GLAccountTypeID' => null,
            'GLTypeGroupID' => null,
            'Source' => 'NIMBLE',
            'PageSize' => $pageSize,
        ];

        if ($nextCursor) {
            $payload['LastCursor'] = $nextCursor;
            $payload['lastCursor'] = $nextCursor;
        }

        return Http::withoutVerifying()
            ->timeout(180)
            ->withHeaders($headers)
            ->post($endpoint, $payload);
    }

    private function mapLedgerRow(array $row, int $userId, $now): ?array
    {
        $glCode = $this->pick($row, ['gLCode', 'GLCode', 'glCode', 'AccountID']);
        if (! $glCode) {
            return null;
        }

        $activeValue = $this->pick($row, ['isActive', 'IsActive', 'active', 'Active']);
        $isActive = filter_var($activeValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isActive === null) {
            $isActive = true;
        }

        return [
            'GLCode' => (string) $glCode,
            'GLName' => (string) ($this->pick($row, ['gLName', 'GLName', 'glName']) ?? ''),
            'GLAccountTypeID' => (string) ($this->pick($row, ['gLAccountTypeID', 'GLAccountTypeID']) ?? ''),
            'GLTypeGroupID' => null,
            'GLSubAccountTypeID' => null,
            'ParentGLID' => null,
            'NormalBalance' => null,
            'IsControlAccount' => 0,
            'IsPostingAccount' => 1,
            'CBSAccountCode' => null,
            'BranchID' => (string) ($this->pick($row, ['bankID', 'BankID']) ?? ''),
            'GLAccountTypeValue' => (string) ($this->pick($row, ['gLAccountTypeName', 'GLAccountTypeName']) ?? ''),
            'GLTypeGroupIDValue' => (string) ($this->pick($row, ['gLTypeGroupID', 'GLTypeGroupID']) ?? ''),
            'GLTypeGroupValue' => (string) ($this->pick($row, ['gLTypeGroupID', 'GLTypeGroupID']) ?? ''),
            'GLSubAccountTypeIDValue' => (string) ($this->pick($row, ['gLSubAccountTypeID', 'GLSubAccountTypeID']) ?? ''),
            'GLDigits' => null,
            'Description' => (string) ($this->pick($row, ['description', 'Description']) ?? ''),
            'IsActive' => $isActive ? 1 : 0,
            'CurrencyID' => 56,
            'Source' => (string) ($this->pick($row, ['source', 'Source']) ?? 'NIMBLE'),
            'SourceTable' => (string) ($this->pick($row, ['sourceTable', 'SourceTable']) ?? ''),
            'IsSynced' => 1,
            'CreatedBy' => $userId,
            'CreatedOn' => $now,
            'ModifiedBy' => $userId,
            'ModifiedOn' => $now,
            'DeletedBy' => null,
            'DeletedOn' => null,
        ];
    }

    private function pick(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return null;
    }
}
