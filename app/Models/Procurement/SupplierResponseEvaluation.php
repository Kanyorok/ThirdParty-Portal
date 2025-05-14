<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\RFQEvaluation;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

     public function rfqEvaluations()
    {
        return $this->belongsToMany(RFQEvaluation::class, 't_RFQEvaluation_Evaluation', 'EvaluationId', 'RFQEvaluationId')
                    ->withTimestamps();
    }
}
