<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class SupplierResponseEvaluation extends Model
{
    protected $table = 't_SupplierResponseEvaluations';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQEvaluationId',
        'SupplierId',
        'TechnicalQuality',
        'TechnicalQualityComments',
        'Pricing',
        'PricingComments',
        'DeliveryTime',
        'DeliveryTimeComments',
        'PastExperience',
        'PastExperienceComments',
        'CreatedBy',
        'ModifiedBy',
    ];

    public function rfqEvaluation()
    {
        return $this->belongsTo(RFQEvaluation::class, 'RFQEvaluationId', 'Id');
    }
}
