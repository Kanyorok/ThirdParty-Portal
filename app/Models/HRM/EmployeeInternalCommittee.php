<?php

namespace App\Models\HRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\HRM\Employee;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class EmployeeInternalCommittee extends Model
{

    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_Committee_Employee';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $fillable = [
        'CommitteeId',
        'EmployeeId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function employee():BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeId', 'Id');
    }
}
