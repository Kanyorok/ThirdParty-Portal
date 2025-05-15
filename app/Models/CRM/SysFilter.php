<?php

namespace App\Models\CRM;

use App\Enums\Core\ComparisonOperatorsEnum;
use App\Enums\Core\DataTypesEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysFilter extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_SysFilters';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'Name',
                           'FieldName',
                           'Relation',
                           'RelationSource',
                           'DataType',
                           'Operator',
                           'Source',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'DataType' => DataTypesEnum::class,
                        'Operator' => ComparisonOperatorsEnum::class,
                       ];
}
