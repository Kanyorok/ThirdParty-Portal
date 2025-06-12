<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriverProjectionsData extends Model
{
     use UserActorTrait,SoftDeletes;

    protected $table='t_BudgetDriverProjectionsData';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriverProjectionsDataId';
    }

    protected $fillable =[
        'BudgetDriverProjectionsID',
        'ProductID',
        'Volume',
        'Value',
        'CreatedBy',
        'ModifiedBy',
    ];
}
