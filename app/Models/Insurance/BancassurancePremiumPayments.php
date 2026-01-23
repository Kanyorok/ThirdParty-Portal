<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Insurance\BancassurancePolicy;
use App\Traits\Model\UserActorTrait;

class BancassurancePremiumPayments extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_BancassurancePremiumPayments';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyID',
        'CustomerID',
        'PaymentFrequency',
        'PaymentDate',
        'NextPaymentDate',
        'Amount',
        'CurrencyId',
        'PaymentMode',
        'ReferenceNumber',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'BancassurancePremiumPaymentsId';
    }

    public function policies()
    {
        return $this->belongsTo(BancassurancePolicy::class, 'PolicyID', 'ID');
    }

    public function paymentModes()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMode', 'ID');
    }

    public function customer()
    {
        return $this->belongsTo(BancassuranceCustomer::class, 'CustomerID', 'Id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }
}
