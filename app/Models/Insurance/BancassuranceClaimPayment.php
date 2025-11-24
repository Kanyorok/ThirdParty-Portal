<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaimPayment extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaimPayments';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'ClaimId', 'PaymentDate', 'PaymentAmount', 'PaymentReference', 'Note',
        'PaidBy', 'PaymentMethod', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassuranceclaimpaymentId';
    }

    public function claim()
    {
        return $this->belongsTo(BancassuranceClaim::class, 'ClaimId', 'Id');
    }

    public function payment()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMethod', 'ID');
    }
}
