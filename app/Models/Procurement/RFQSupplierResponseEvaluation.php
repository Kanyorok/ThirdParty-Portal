<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQSupplierResponseEvaluation extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_RFQSupplierResponseEvaluations';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'RFQSupplierResponseEvaluationsId';
    }

    protected $fillable = [
        'RFQEvaluationId',
        'SupplierId',
        'CriteriaId',
        'Score',
        'Comments',
        'CreatedBy',
        'ModifiedBy',
    ];

    public function rfqEvaluation()
    {
        return $this->belongsTo(RFQEvaluation::class, 'RFQEvaluationId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }

    public function criteria()
    {
        return $this->belongsTo(RFQSettingCriteria::class, 'CriteriaId', 'id');
    }

    public function getTotalQuotedAttribute()
    {
        if (!$this->relationLoaded('rfqEvaluation')) {
            $this->load('rfqEvaluation');
        }

        return RFQResponse::where('SupplierId', $this->SupplierId)
            ->where('RFQId', $this->rfqEvaluation->RFQId ?? 0)
            ->value('TotalPayable');
    }

    public function getDeliveryTimeAttribute()
    {
        return RFQResponse::where('SupplierId', $this->SupplierId)
            ->where('RFQId', $this->rfqEvaluation->RFQId ?? 0)
            ->value('DurationDays');
    }

    public function rfqCriteria()
    {
        return $this->hasOne(RFQCriteria::class, 'CriteriaID', 'CriteriaId')
            ->where(function ($query) {
                if ($this->rfqEvaluation) {
                    $query->where('RFQID', $this->rfqEvaluation->RFQId);
                }
            });
    }

    public function rfqCriteriaUnscoped()
    {
        return $this->belongsTo(RFQCriteria::class, 'CriteriaId', 'CriteriaID');
    }

}
