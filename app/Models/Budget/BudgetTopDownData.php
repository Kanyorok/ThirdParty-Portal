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

    protected $table = 't_BudgetTopDownData'; // Use your actual table name
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'BudgetTopDownDataId';
    }

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'TopDownID',
        'BranchID',
        'AllocationPercentage',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

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
