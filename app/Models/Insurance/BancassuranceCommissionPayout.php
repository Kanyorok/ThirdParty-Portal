<?php

namespace App\Models\Insurance;

use App\Enums\Insurance\InsurancePolicyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;

class BancassuranceCommissionPayout extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_BancassuranceCommissionPayouts';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId',
        'PayoutReference',
        'PaidAmount',
        'PaymentDate',
        'PaymentMode',
        'Remarks',
        'PaidBy',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
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

}
