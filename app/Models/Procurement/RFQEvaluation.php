<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\RFQ;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQEvaluation extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_RFQEvaluation';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId',
        'RFQEvaluateNumber',
        'CommitteeMemberName',
        'TechnicalQualityScore',
        'PricingScore',
        'DeliveryTimeScore',
        'PastExperienceScore',
        'Comment',
        'SupplierId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'DeletedOn',
        'CreatedOn',
        'ModifiedOn',
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }
}
