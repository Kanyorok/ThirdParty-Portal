<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class BudgetLineLink extends Model
{
     use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

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
    public function budgetMaster()
{
    return $this->belongsTo(BudgetMaster::class, 'BudgetLineID', 'BudgetLineID');
}

}
