<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetGLAccountSubType extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetGLSubTypes';
    protected $primaryKey = 'Id';
    //        'GLAccountTypeValue',
    //        'GLAccountSubTypeName',
    //        'CreatedBy',
    //        'ModifiedBy',

    public static function getPrimaryKey(): string
    {
        return 'GLAccountSubTypesId';
    }
}
