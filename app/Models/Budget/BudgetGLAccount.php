<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetGLAccount extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_BudgetGLAccounts';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetGLAccountId';
    }


    protected $fillable = [
        'CurrencyID',
        'GLName',
        'Description',
        'GTType',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function budgetLines()
    {
        return $this->belongsToMany(BudgetLine::class, 't_BudgetLinesGLAccounts', 'BudgetGLAccountID', 'BudgetLineID')
            ->withTimestamps()
            ->withPivot(['CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn']);
    }
}
