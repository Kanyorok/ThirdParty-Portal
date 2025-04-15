<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BoardCommittee extends Pivot
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_BoardCommittee';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'BoardId',
                           'CommitteeId',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'CommitteeId', 'Id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'BoardId', 'Id');
    }
}
