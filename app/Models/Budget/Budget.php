<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_Budgets';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'Name',
        'FiscalYear',
        'From',
        'To',
        'Notes',
        'Status',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $cast = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];


    public static function getPrimaryKey(): string
    {
        return 'BudgetId';
    }

    public function activities()
    {
        return $this->hasMany(BudgetActivity::class, 'BudgetId', 'Id');
    }
}
