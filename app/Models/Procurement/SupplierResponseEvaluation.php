<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class SupplierResponseEvaluation extends Model
{
    protected $table = 't_SupplierResponseEvaluations';
     protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
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
}
