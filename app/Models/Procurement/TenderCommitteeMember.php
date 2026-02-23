<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCommitteeMember extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderCommitteeMembers';

    protected $fillable = [
        'CommitteeID',
        'UserID',
        'TenderID',
        'Role',
        'PendingRole',
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
        'PendingRole' => 'string',
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

    protected $primaryKey = 'id';

    // Compatibility bridge: DB uses lowercase `id`, legacy code often reads/writes `Id`.
    public function getIdAttribute()
    {
        return $this->attributes['id'] ?? null;
    }

    public function setIdAttribute($value): void
    {
        $this->attributes['id'] = $value;
    }

    public function committee()
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

    public function tenderCommitteeEvaluations()
    {
        return $this->hasMany(TenderCommitteeEvaluation::class, 'MemberID', 'Id');
    }

    public function roleHistory()
    {
        return $this->hasMany(CommitteeRoleHistory::class, 'MemberID', 'id')
            ->where('MemberType', 'tender')
            ->orderByDesc('ChangedOn');
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
        // Prefer via user->employee when UserID stores User.Id
        return $this->user()?->employee();
    }

    public function user()
    {
        // Standard mapping: UserID stores User.Id
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function userByEmployee()
    {
        // Backward-compat: some records saved Employee.Id into UserID
        return $this->belongsTo(User::class, 'UserID', 'EmployeeId');
    }
}
