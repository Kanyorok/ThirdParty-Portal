<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLineLink extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_BudgetLineLink';
    protected $primaryKey = 'LinkID';

    protected $fillable = [
        'LinkID',
        'LineItemID',
        'BudgetLineID',
        'AmountAllocated',
        'LinkedBy',
        'LinkedDate',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineLinkID';
    }
    public function budgetMaster()
    {
        return $this->belongsTo(BudgetMaster::class, 'BudgetLineID', 'BudgetLineID');
    }

}
