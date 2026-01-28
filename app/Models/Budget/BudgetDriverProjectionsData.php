<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriverProjectionsData extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_BudgetDriverProjectionsData';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriverProjectionsDataId';
    }

    protected $fillable = [
        'BudgetDriverProjectionsID',
        'ProductID',
        'Volume',
        'Value',
        'CreatedBy',
        'ModifiedBy',
    ];

    public function productType()
    {
        return $this->belongsTo(BudgetProductType::class, 'ProductID', 'Id');
    }
}
