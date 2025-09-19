<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCommitteeEvaluation extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_TenderCommitteeEvaluations';
    protected $fillable = [
        'CommitteeID',
        'TenderID',
        'MemberID',
        'SectionID',
        'CriteriaID',
        'Score',
        'MaxScore',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CommitteeID' => 'integer',
        'TenderID' => 'integer',
        'MemberID' => 'integer',
        'SectionID' => 'integer',
        'CriteriaID' => 'integer',
        'Score' => 'decimal:2',
        'MaxScore' => 'decimal:2',
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

    public function tenderCommittee()
    {
        return $this->belongsTo(TenderCommittee::class, 'CommitteeID', 'Id');
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

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }

    public function tenderCommitteeMember()
    {
        return $this->belongsTo(TenderCommitteeMember::class, 'MemberID', 'Id');
    }
    
    public function section()
    {
        return $this->belongsTo(\App\Models\procurement\Section::class, 'SectionID', 'Id');
    }
    
    public function criteria()
    {
        return $this->belongsTo(\App\Models\procurement\Criteria::class, 'CriteriaID', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'TenderCommitteeEvaluationID';
    }
}
