<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProduct extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table='t_BudgetProducts';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    protected $fillable = [
        'CBSProductID',
        'Description',
        'ProductTypeID',
        'CurrencyID',
        'GLAccountID',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];
    
    protected $casts = [
        'CreatedOn'  => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn'  => 'datetime',
    ];


}
