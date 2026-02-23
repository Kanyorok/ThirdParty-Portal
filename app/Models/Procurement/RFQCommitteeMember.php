<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQCommitteeMember extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_RFQCommitteeMembers';

    protected $primaryKey = 'Id';

    // Compatibility bridge: DB uses lowercase `id`, legacy code often reads/writes `Id`.
    public function getIdAttribute()
    {
        return $this->attributes['id'] ?? null;
    }

    public function setIdAttribute($value): void
    {
        $this->attributes['id'] = $value;
    }

    protected $fillable = [
        'CommitteeID',
        'UserID',
        'RFQID',
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
        'RFQID' => 'integer',
        'Role' => 'string',
        'PendingRole' => 'string',
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

    public function roleHistory()
    {
        return $this->hasMany(CommitteeRoleHistory::class, 'MemberID', 'id')
            ->where('MemberType', 'rfq')
            ->orderByDesc('ChangedOn');
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
        $user = $this->user ?: $this->userByEmployee;

        return $user?->employee();
    }

    public function getCommitteeMemberNameAttribute(): string
    {
        return $this->employee?->full_name ?? 'Unknown';
    }

    public function user()
    {
        // Standard mapping: UserID stores User.Id
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function userByEmployee()
    {
        // Backward-compat: some legacy records saved Employee.Id into UserID
        return $this->belongsTo(User::class, 'UserID', 'EmployeeId');
    }
}
