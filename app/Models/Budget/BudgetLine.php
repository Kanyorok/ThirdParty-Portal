<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLine extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table='t_BudgetLines';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    protected $fillable = [
        'LineName',
        'Description',
        'IsDefault',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'IsDefault'   => 'boolean',
        'CreatedOn'   => 'datetime',
        'ModifiedOn'  => 'datetime',
        'DeletedOn'   => 'datetime',
    ];

}
