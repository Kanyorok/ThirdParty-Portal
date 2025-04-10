<?php

namespace App\Models;

use App\Enums\Core\ComparisonOperatorsEnum;
use App\Enums\Core\DataTypesEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysFilter extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_SysFilters';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Name', 'FieldName', 'Relation', 'RelationSource', 'DataType', 'Operator', 'Source',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'DataType' => DataTypesEnum::class,
        'Operator' => ComparisonOperatorsEnum::class,
    ];
}
