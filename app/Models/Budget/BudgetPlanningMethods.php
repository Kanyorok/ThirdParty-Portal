<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPlanningMethods extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetPlanningMethodsId';
    }


    protected $table = 't_BudgetPlanningMethods';

    protected $fillable = [

        'MethodName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $cast = [
        'IsActive' => 'boolean',
    ];
}
