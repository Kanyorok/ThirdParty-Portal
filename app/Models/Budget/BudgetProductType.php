<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProductType extends Model
{
    use UserActorTrait,SoftDeletes;
    
    protected $table='t_BudgetProductTypes';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'CreatedOn'     => 'datetime',
        'LastSyncDate'  => 'datetime',
        'ModifiedOn'    => 'datetime',
        'DeletedOn'     => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(BudgetProduct::class, 'CBSProductID', 'Id');
    }

}
