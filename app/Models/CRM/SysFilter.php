<?php

namespace App\Models\CRM;

use App\Enums\Core\ComparisonOperatorsEnum;
use App\Enums\Core\DataTypesEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysFilter extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_SysFilters';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'SysFiltersId';
    }

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
