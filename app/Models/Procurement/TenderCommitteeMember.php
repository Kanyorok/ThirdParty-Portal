<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCommitteeMember extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderCommitteeMembers';
    protected $fillable = [
        'CommitteeID',
        'UserID',
        'TenderID',
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
        'TenderID' => 'integer',
        'Role' => 'string',
        'Response' => 'integer',
        'IsActive' => 'boolean',
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

    protected $primaryKey = 'Id';

    public function committee()
    {
        return $this->belongsTo(TenderCommittee::class, 'TenderCommitteeID', 'Id');
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

    public function tenderCommitteeEvaluations()
    {
        return $this->hasMany(TenderCommitteeEvaluation::class, 'TenderCommitteeMemberID', 'Id');
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'TenderCommitteeMemberID';
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'UserID', 'Id');
    }
}
