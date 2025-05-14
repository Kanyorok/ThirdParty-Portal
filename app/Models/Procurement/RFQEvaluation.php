<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\SupplierResponseEvaluation;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQEvaluation extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_RFQEvaluations';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'CommitteeMemberName',
        'UserCode',
        'RFQId',
        'RFQComment',
        'Confirmation',
        'CreatedBy',
        'ModifiedBy',
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }

     public function evaluations()
    {
        return $this->belongsToMany(SupplierResponseEvaluation::class, 't_RFQEvaluation_Evaluation', 'RFQEvaluationId', 'EvaluationId')
                    ->withTimestamps();
    }
}
