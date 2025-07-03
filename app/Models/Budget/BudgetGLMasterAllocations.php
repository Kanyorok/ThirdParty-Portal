<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetGLMasterAllocations extends Model
{
    use UserActorTrait,SoftDeletes;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    public static function getPrimaryKey(): string
    {
        return 'BudgetGLMasterAllocationsID';
    }
    protected $table = 't_BudgetGLMasterAllocations';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'BudgetID',
        'BranchID',
        'GLAttachmentID',
        'AccountID',
        'Description',
        'GLAccountTypeID',
        'Month1', 'Month2', 'Month3', 'Month4', 'Month5', 'Month6',
        'Month7', 'Month8', 'Month9', 'Month10', 'Month11', 'Month12',
        'Total',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function glType(){
        return $this->belongsTo(BudgetGLMaster::class, 'AccountID', 'AccountID');
    }
}
