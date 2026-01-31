<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceCommissionRule extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_BancassuranceCommissionRules';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
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
        'DeletedBy',
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
