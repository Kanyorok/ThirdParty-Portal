<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Budget\BudgetProductType;

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
        return 'BudgetProductId';
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

    public function productType()
    {
        return $this->belongsTo(BudgetProductType::class, 'CBSProductID', 'Id');
    }

    public function glAccount(){
        return $this->belongsTo(BudgetGLAccount::class,'GLAccountID','Id');
    }

}
