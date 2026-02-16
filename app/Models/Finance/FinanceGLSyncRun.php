<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;

class FinanceGLSyncRun extends Model
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    protected $table = 't_FinanceGLSyncRuns';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Source',
        'Status',
        'Message',
        'SyncError',
        'RecordsSynced',
        'RecordsFailed',
        'TotalRecords',
        'CurrentPage',
        'PageSize',
        'LastCursor',
        'StartedAt',
        'CompletedAt',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'StartedAt' => 'datetime',
        'CompletedAt' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceGLSyncRunId';
    }
}
