<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProductType extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_BudgetProductTypes';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetProductTypeId';
    }

    protected $fillable = [
        'ProductCode',
        'Name',
        'Description',
        'CBSCode',
        'CreatedOn',
        'LastSyncDate',
        'CreatedBy',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'LastSyncDate' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(BudgetProduct::class, 'CBSProductID', 'Id');
    }
}
