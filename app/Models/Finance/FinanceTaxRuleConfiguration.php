<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTaxRuleConfiguration extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = "t_FinanceTaxRuleConfiguration";
    protected $primaryKey = 'Id';
    protected $fillable = [
        'TaxTypeId',
        'JurisdictionId',
        'Rate',
        'AppliesTo',
        'ThresholdAmount',
        'ApplyTaxPer',
        'EffectiveFrom',
        'EffectiveTo',
        'TaxPayableGLID',
        'TaxReceivableGLID',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceTaxRuleConfigurationId';
    }

    public function taxType()
    {
        return $this->belongsTo(FinanceTaxType::class, 'TaxTypeId','Id');
    }

    public function jurisdiction()
    {
        return $this->belongsTo(TaxJurisdiction::class, 'JurisdictionId', 'Id');
    }

    public function taxPayableGLAccount()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'TaxPayableGLID', 'Id');
    }

    public function taxReceivableGLAccount()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'TaxReceivableGLID', 'Id');
    }
}
