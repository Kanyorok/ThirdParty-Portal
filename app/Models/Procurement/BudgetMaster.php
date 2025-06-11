<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineID';
    }
    public function budgetLineLinks()
    {
        return $this->hasMany(BudgetLineLink::class, 'BudgetLineID', 'BudgetLineID');
    }

}

