<?php

namespace App\Models\ThirdParies;

use App\Models\HRM\Committee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BoardCommittee extends Pivot
{
    use UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';

    protected $table = 't_BoardCommittee';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'BoardCommitteeId';
    }

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
