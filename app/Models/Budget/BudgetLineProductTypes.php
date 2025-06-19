<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetLineProductTypes extends Model
{
    protected $table = 't_BudgetLineProductTypes';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineProductTypesId';
    }

    protected $fillable = [
        'BudgetLineId',
        'ProductTypeId',
        'CreatedBy',
        'ModifiedBy',
    ];
    
}
