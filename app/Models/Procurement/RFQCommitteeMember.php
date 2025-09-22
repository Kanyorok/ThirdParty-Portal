<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HRM\Employee;
use App\Models\Auth\User;

class RFQCommitteeMember extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_RFQCommitteeMembers';

    protected $primaryKey = 'Id';

    protected $fillable = [
        'CommitteeID',
        'UserID',
        'RFQID',
        'Role',
        'Response',
        'IsActive',
        'HasEvaluated',
        'reason',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CommitteeID' => 'integer',
        'UserID' => 'integer',
        'RFQID' => 'integer',
        'Role' => 'string',
        'Response' => 'integer',
        'IsActive' => 'boolean',
        'HasEvaluated' => 'boolean',
        'CreatedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedBy' => 'integer',
        'ModifiedOn' => 'datetime',
        'DeletedBy' => 'integer',
        'DeletedOn' => 'datetime',
    ];

    protected $dates = [
        'CreatedOn',
        'ModifiedOn',
        'DeletedOn',
    ];

    public function committee()
    {
        return $this->belongsTo(RFQCommittee::class, 'CommitteeID', 'Id');
    }

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQID', 'Id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'RFQCommitteeMemberID';
    }

    public function employee()
    {
        return $this->user?->employee();
    }

    public function getCommitteeMemberNameAttribute(): string
    {
        return $this->employee?->full_name ?? 'Unknown';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'EmployeeId');
    }


}
