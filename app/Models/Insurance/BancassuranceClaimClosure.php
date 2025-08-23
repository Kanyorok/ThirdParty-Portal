<?php

namespace App\Models\Insurance;

use App\Enums\Insurance\InsuranceClosureEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaimClosure extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaimClosures';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ClaimId','FinalStatus','FinalRemarks','ClosureDate','ClosedBy',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassuranceclaimclosureId';
    }

    protected $casts = [
        'FinalStatus' => InsuranceClosureEnum::class,
    ];

    public function claim()
    {
        return $this->belongsTo(BancassuranceClaim::class, 'ClaimId', 'Id');
    }
    public function paidamount()
    {
        return $this->hasOne(BancassuranceClaimPayment::class, 'ClaimId', 'ClaimId');
    }
}
