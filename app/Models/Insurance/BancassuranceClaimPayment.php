<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaimPayment extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaimPayments';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'ClaimId', 'PaymentDate', 'PaymentAmount', 'PaymentReference', 'Note',
        'PaidTo', 'PaymentMethod', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
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
