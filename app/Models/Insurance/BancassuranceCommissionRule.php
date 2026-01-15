<?php

namespace App\Models\Insurance;

use App\Models\Core\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;

class BancassuranceCommissionRule extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_BancassuranceCommissionRules';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RuleName',
        'ProductId',
        'PolicyTypeId',
        'CommissionRate',
        'FixedAmount',
        'CurrencyId',
        'AppliesTo',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'BancassuranceCommissionRulesId';
    }

    public function product()
    {
        return $this->belongsTo(InsuranceProduct::class, 'ProductId', 'Id');
    }

    public function policytypes()
    {
        return $this->belongsTo(CodeDetail::class, 'PolicyTypeId', 'ID');
    }

    public function appliesto()
    {
        return $this->belongsTo(CodeDetail::class, 'AppliesTo', 'ID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }
}
