<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;


class PropertyLeaseTermination extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;
    protected $table = 't_TerminateLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseID',
        'TerminationDate',
        'TerminationReason',
        'Remarks',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'LeaseTerminationId';
    }

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
