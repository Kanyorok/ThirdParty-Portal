<?php

namespace App\Services\Core;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\CodeDetail;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

abstract class ApprovalWorkflowService
{
    /**
     * @throws ErroredException
     */
    public static function codeDetail(BackedEnum $status, string $CodeID): CodeDetail
    {
        $code = CodeDetail::query()->where('CodeID', $CodeID)->where('Value', $status->value)->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        throw new ErroredException("Invalid Status : {$status ->value} for codeID: {$CodeID} ");
    }

    /**
     * @param User $actor
     * @param CodeDetail $status //status to
     * @param string $source Class::getPrimaryKey
     * @param string|int $sourceId Class primary id
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    protected function approveAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn = 'Status'): bool
    {
        return $this->_execute($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    /**
     * @throws ErroredException
     */
    private function _execute(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn): bool
    {
        $class = Relation::getMorphedModel($source);
        if ($class && class_exists($class)) {
            $table = (new $class)->getTable();
        } else {
            throw new ErroredException('Invalid Related Entity');
        }

        /**
         * @Source NVARCHAR(255) => Table name,
         * @SourceID NVARCHAR(100), => primaryKey Value
         * @UserID BIGINT, => Actor Id
         * @UserName NVARCHAR(255) = NULL, => Name / UserID
         * @Notes NVARCHAR(MAX) = NULL, => Remarks/Comments ...
         * @StatusColumn NVARCHAR(100) = 'Status',
         * @StatusID BIGINT => //
         */
        try {
        return DB::statement("EXEC p_ProcessWorkflowAction ?, ?, ?, ?, ?, ?, ?", [
            $table,
            (string)$sourceId,
            $actor->Id,
            $actor->UserID,
            $remarks,
            $statusColumn,
            $status->ID
        ]);
        } catch (QueryException $e) {
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        } catch (Exception $e) {
            throw new ErroredException("Unexpected Error occurred ");
        }

    }

    /**
     * Extract the clean error message from SQL Server RAISERROR
     */
    private function _extractSqlServerError(string $errorMessage): string
    {
        // Pattern 1: Extract message between [SQL Server] and next bracket or end
        if (preg_match('/\[SQL Server\]\s*(.+?)(?:\s*\[|$)/s', $errorMessage, $matches)) {
            $errorMessage = trim($matches[1]);
        } else if (preg_match('/SQLSTATE\[.*?\]:\s*(.+?)(?:\s*\(|$)/s', $errorMessage, $matches)) {
            $errorMessage = trim($matches[1]);
        }


        $cleanMessage = preg_replace('/\(Connection:.*?\)/', '', $errorMessage);
        $cleanMessage = preg_replace('/SQLSTATE\[.*?\]:\s*/', '', $cleanMessage);

        return trim($cleanMessage) ?: 'Database operation failed';
    }


    /**
     * @param User $actor
     * @param CodeDetail $status //status to
     * @param string $source Class::getPrimaryKey
     * @param string|int $sourceId Class primary id
     * @param string $remarks
     * @return bool
     * @throws ErroredException
     */
    protected function submittedAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks /*string $statusColumn = 'Status'*/): bool
    {
        $class = Relation::getMorphedModel($source);
        if ($class && class_exists($class)) {
            $table = (new $class)->getTable();
        } else {
            throw new ErroredException('Invalid Related Entity');
        }

        try {
            $history = WorkflowHistory::create([
                "Source" => $table,
                "SourceID" => (string)$sourceId,
                "StatusId" => $status->ID,
                "Stage" => $status->Description,
                //"Amount",
                "Notes" => $remarks,
                "CreatedBy" => $actor->Id,
                "CreatedOn" => now(),
                "ModifiedBy" => $actor->Id,
                "ModifiedOn" => now(),
            ]);

            // After the outer transaction commits, run the SPs
            DB::afterCommit(function () {
                try {
                    DB::statement("EXEC p_ProcessWorkflowPending");
                    DB::statement("EXEC p_ProcessWorkflowStages");
                } catch (\Throwable $e) {
                    // Log but don't affect already-committed history
                    Log::error('Workflow post-commit SPs failed', ['error' => $e->getMessage()]);
                }
            });

        } catch (QueryException $e) {
            Log::error('WorkflowHistory insert failed', ['error' => $e->getMessage()]);
            throw new ErroredException($this->_extractSqlServerError($e->getMessage()));
        } catch (Exception $e) {
            Log::error($e);
            throw new ErroredException("Unexpected Error Occurred.");
        }

    return (bool) $history;
    }

    /**
     * @param User $actor
     * @param CodeDetail $status //status to
     * @param string $source Class::getPrimaryKey
     * @param string|int $sourceId Class primary id
     * @param string $remarks
     * @param string $statusColumn
     * @return bool
     * @throws ErroredException
     */
    protected function rejectAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks, string $statusColumn = 'Status'): bool
    {
        return $this->_execute($actor, $status, $source, $sourceId, $remarks, $statusColumn);
    }

    /**
     * @param string $source Model::getPrimaryKey
     * @throws ErroredException
     */
    protected function historyData(string $source, int $limit = 1000): Collection
    {
        $class = Relation::getMorphedModel($source);
        if ($class && class_exists($class)) {
            $table = (new $class)->getTable();
        } else {
            throw new ErroredException('Invalid Related Entity');
        }

        return WorkflowHistory::query()->where('Source', $table)->with(['creator', 'status'])->limit($limit)->get();
    }
}
