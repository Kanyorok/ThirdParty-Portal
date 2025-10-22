<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetGLAccountSubType extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetGLSubTypes';
    protected $primaryKey = 'Id';
//    protected $fillable = [
//        'GLAccountTypeValue',
//        'GLAccountSubTypeName',
//        'CreatedBy',
//        'ModifiedBy',
//    ];

    public static function getPrimaryKey(): string
    {
        return 'GLAccountSubTypesId';
    }

}
