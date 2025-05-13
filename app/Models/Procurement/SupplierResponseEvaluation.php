<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class SupplierResponseEvaluation extends Model
{
    protected $table = 't_SupplierResponseEvaluations';

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
    ];
}
