<?php

namespace App\Models\Finance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxJurisdiction extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = "t_FinanceTaxJurisdiction";
    protected $primaryKey = 'Id';

    protected $fillable = [
        'JurisdictionName',
        'Currency',
        'TaxAuthority',
        'Status',
        'CreatedBy',
        'ModifiedBy'
    ];

     public static function getPrimaryKey(): string
    {
        return 'TaxJurisdictionId';
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'Currency', 'Id');
    }
}
