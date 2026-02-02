<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQEvaluation extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_RFQEvaluations';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'RFQEvaluationsId';
    }
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
        return $this->hasMany(RFQSupplierResponseEvaluation::class, 'RFQEvaluationId', 'Id');
    }
}
