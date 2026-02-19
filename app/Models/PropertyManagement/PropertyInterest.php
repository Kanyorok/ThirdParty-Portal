<?php

namespace App\Models\PropertyManagement;

use App\Models\Workflow\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyInterest extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    protected $table = 't_PropertyInterest';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyId',
        'BlockId',
        'FloorId',
        'UnitId',
        'TenantId',
        'InterestedStartDate',
        'InterestedEndDate',
        'PaymentFrequency',
        'AdditionalInformation',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyInterestId';
    }

    public function tenant()
    {
        return $this->belongsTo(PropertyNewTenant::class, 'TenantId', 'Id');
    }

    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyId', 'Id');
    }

    public function block()
    {
        return $this->belongsTo(PropertyBlock::class, 'BlockId', 'Id');
    }

    public function floor()
    {
        return $this->belongsTo(PropertyFloor::class, 'FloorId', 'Id');
    }

    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'UnitId', 'Id');
    }

    public function code()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentFrequency', 'ID');
    }
    public function price()
    {
        return $this->hasOne(PropertyRateAndPricing::class, 'UnitId', 'UnitId');
    }
}
