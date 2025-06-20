<?php

namespace App\Models\Budget;

use App\Http\Controllers\Budget\BudgetTopDownAllocationController;
use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetTopDownData extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn'; // Use your actual table name
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetTopDownData';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'TopDownID',
        'BranchID',
        'AllocationPercentage',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetTopDownDataId';
    }

    // Relationships

    public function topDown()
    {
        return $this->belongsTo(BudgetTopDown::class, 'TopDownID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID'); // Adjust namespace if needed
    }
}
