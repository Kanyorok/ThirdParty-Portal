<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetLineProductTypes extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetLineProductTypes';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'BudgetLineId',
        'ProductTypeId',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineProductTypesId';
    }

}
