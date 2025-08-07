<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'PaymentDate',
        'Amount',
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
        return $this->belongsTo(BancassurancePolicies::class, 'PolicyID', 'ID');
    }
    public function paymentModes()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMode', 'ID');
    }
}