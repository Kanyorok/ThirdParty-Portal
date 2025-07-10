<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementMethod extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_ProcurementMethod';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'MethodId', 'ApprovedPlanId', 'ApprovedPlanLineId', 'AssignedMethod', 'Justification',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

}

