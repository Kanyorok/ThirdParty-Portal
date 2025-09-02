<?php

namespace App\Services\Core;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\CodeDetail;
use BackedEnum;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        throw new ErroredException('Invalid Status');
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
        return DB::statement("EXEC p_ProcessWorkflowAction ?, ?, ?, ?, ?, ?, ?", [
            $table,
            (string)$sourceId,
            $actor->Id,
            $actor->UserID,
            $remarks,
            $statusColumn,
            $status->ID
        ]);
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
    protected function submittedAction(User $actor, CodeDetail $status, string $source, string|int $sourceId, string $remarks /*string $statusColumn = 'Status'*/): bool
    {
        $class = Relation::getMorphedModel($source);
        if ($class && class_exists($class)) {
            $table = (new $class)->getTable();
        } else {
            throw new ErroredException('Invalid Related Entity');
        }

        WorkflowHistory::create([
            "Source" => $table,
            "SourceID" => (string)$sourceId,
            "StatusId" => $status->ID,
            "Stage" => $status->Description,
            //"Amount",
            "Notes" => $remarks,
            "CreatedBy" => $actor->Id,
            "ModifiedBy" => $actor->Id,
        ]);

        /// execute pending SP
        DB::statement("EXEC p_ProcessWorkflowPending");
        //$ExecPendingWorkFlow = '';
        /// //Execeute stages SP
        // $ExecSatgesWorkFlow = '';
        DB::statement("EXEC p_ProcessWorkflowStages");

        /*return DB::statement("EXEC p_ProcessWorkflowAction ?, ?, ?, ?, ?, ?, ?", [
            $table,
            (string)$sourceId,
            $actor->Id,
            $actor->UserID,
            $remarks,
            $statusColumn,
            $status->ID
        ]);*/


        return true;
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
