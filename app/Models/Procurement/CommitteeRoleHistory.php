<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class CommitteeRoleHistory extends Model
{
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    /** Response status constants */
    public const STATUS_PENDING = 0;
    public const STATUS_ACCEPTED = 1;
    public const STATUS_DECLINED = 2;

    protected $table = 't_CommitteeRoleHistory';

    protected $fillable = [
        'MemberType',
        'MemberID',
        'CommitteeID',
        'PreviousRole',
        'NewRole',
        'Status',
        'ChangedBy',
        'ChangedOn',
        'RespondedOn',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'MemberID' => 'integer',
        'CommitteeID' => 'integer',
        'Status' => 'integer',
        'ChangedBy' => 'integer',
        'ChangedOn' => 'datetime',
        'RespondedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * The user who made the role change.
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'ChangedBy', 'Id');
    }

    /**
     * Resolve the member record depending on MemberType.
     */
    public function member()
    {
        return match ($this->MemberType) {
            'tender' => $this->belongsTo(TenderCommitteeMember::class, 'MemberID', 'id'),
            'rfq' => $this->belongsTo(RFQCommitteeMember::class, 'MemberID', 'id'),
            default => null,
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    public function isPending(): bool
    {
        return (int) $this->Status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return (int) $this->Status === self::STATUS_ACCEPTED;
    }

    public function isDeclined(): bool
    {
        return (int) $this->Status === self::STATUS_DECLINED;
    }

    public function statusLabel(): string
    {
        return match ((int) $this->Status) {
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_DECLINED => 'Declined',
            default => 'Pending',
        };
    }
}
