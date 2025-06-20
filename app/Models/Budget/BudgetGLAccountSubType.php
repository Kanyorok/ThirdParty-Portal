<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetGLAccountSubType extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table='t_GLAccountSubTypes';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'GLAccountSubTypesId';
    }


    protected $fillable = [
        'GLAccountTypeValue',
        'GLAccountSubTypeName',
        'CreatedBy',
        'ModifiedBy',
    ];

}
