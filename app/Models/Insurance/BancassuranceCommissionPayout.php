<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceCommissionPayout extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_BancassuranceCommissionPayouts';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId',
        'PayoutReference',
        'PaidAmount',
        'CurrencyId',
        'CommissionRuleId',
        'PaymentDate',
        'PaymentMode',
        'Remarks',
        'PaidTo',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BancassuranceCommissionPayoutId';
    }

    public function policies()
    {
        return $this->belongsTo(BancassurancePolicy::class, 'PolicyId', 'Id');
    }

    public function paymentmodes()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMode', 'ID');
    }

    public function currencies()
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }
}
