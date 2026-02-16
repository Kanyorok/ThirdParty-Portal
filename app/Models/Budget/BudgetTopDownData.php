<?php

namespace App\Models\Budget;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetTopDownData extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn'; // Use your actual table name
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
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
