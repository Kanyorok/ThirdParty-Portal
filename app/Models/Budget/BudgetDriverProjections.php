<?php

namespace App\Models\Budget;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriverProjections extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_BudgetDriverProjections';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriverProjectionsId';
    }

    protected $fillable = [
        'BudgetID',
        'CurrencyID',
        'CreatedBy',
        'ModifiedBy',
    ];

    // Relationships
    public function projections(): HasMany
    {
        return $this->hasMany(BudgetDriverProjectionsData::class, 'BudgetDriverProjectionsID', 'Id');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'Id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(BudgetPeriods::class, 'PeriodID', 'Id');
    }

    public function productType()
    {
        return $this->belongsTo(BudgetProductType::class, 'ProductID', 'Id');
    }

}
