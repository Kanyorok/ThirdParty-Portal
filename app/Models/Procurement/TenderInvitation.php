<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderInvitation extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderInvitations';

    // Mass assignable attributes
    protected $fillable = [
        'TenderID',
        'SupplierID',
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
    public function tenderID()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }
    public function supplierID()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID', 'Id');
    }


}
