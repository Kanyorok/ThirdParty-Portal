<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderInvitation extends Pivot
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderInvitations';
    protected $primaryKey = 'InvitationID';
    public $incrementing = true;

    public static function getPrimaryKey(): string
    {
        return 'InvitationID';
    }

    // Mass assignable attributes
    protected $fillable = [
        'TenderId',
        'SupplierId',
        'InvitationDate',
        'ResponseStatus',
        'ResponseDate',
        'DeclineReason',
        'ConfirmationAttachment',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    // Casts for automatic type conversion
    protected $casts = [
        'InvitationDate' => 'datetime',
        'ResponseDate' => 'datetime',
    ];

    // Enum-like accessor for response status
    public const STATUS_PENDING = 'Pending';
    public const STATUS_ACCEPTED = 'Accepted';
    public const STATUS_DECLINED = 'Declined';

    //Relationships
    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }
}
