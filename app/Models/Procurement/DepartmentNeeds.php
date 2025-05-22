<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HRM\Department;
use App\Models\Core\Branch;

class DepartmentNeeds extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_DepartmentNeeds';
    protected $primaryKey = 'Id';

    protected $fillable = [
           'NeedID','BranchID','DepartmentID','ItemID','RequestedQty','EstimatedUnitCost',
           'Justification','Status','FiscalYear','RequestedDate','PriorityLevel','IsEmergency',
           'CreatedBy','ModifiedBy','DeletedBy'
    ] ;

    public static function getPrimaryKey(): string{
        return 'DepartmentNeedID';
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID');
    }
    public function department()
{
    return $this->belongsTo(Department::class, 'DepartmentID');
}
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

}
