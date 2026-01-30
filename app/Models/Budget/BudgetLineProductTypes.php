<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLineProductTypes extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
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

    public function products()
    {
        return $this->belongsTo(BudgetProductType::class, 'ProductTypeId', 'Id');
    }

    public function product()
    {
        return $this->belongsTo(BudgetProduct::class, 'ProductTypeId', 'Id');
    }
}
