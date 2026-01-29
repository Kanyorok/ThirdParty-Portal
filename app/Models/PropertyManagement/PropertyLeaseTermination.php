<?php

namespace App\Models\PropertyManagement;

use App\Enums\Core\ApprovalEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyLeaseTermination extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    protected $table = 't_TerminateLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseID',
        'TerminationDate',
        'TerminationReason',
        'Remarks',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LeaseTerminationId';
    }
    protected $casts = [
        'Status' => ApprovalEnum::class,
    ];

    public function lease()
    {
        return $this->belongsTo(PropertyNewLease::class, 'LeaseID', 'Id');
    }

    public function code()
    {
        return $this->belongsTo(CodeDetail::class, 'TerminationReason', 'ID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }
}
