<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class BudgetMaster extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_BudgetMaster';
    protected $primaryKey = 'BudgetLineID';

    protected $fillable = [
        'BudgetLineID',
        'Code',
        'Description',
        'AllocatedAmount',
        'FiscalYear',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public function budgetLineLinks()
    {
        return $this->hasMany(BudgetLineLink::class, 'BudgetLineID', 'BudgetLineID');
    }

}

