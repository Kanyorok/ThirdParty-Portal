<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriodTypes extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetPeriodTypesId';
    }


    protected $table = 't_BudgetPeriodTypes';

    protected $fillable = [

        'PeriodType',
        'Code',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $cast = [
        'IsActive' => 'boolean',
    ];
}
