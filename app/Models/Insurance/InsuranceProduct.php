<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceProduct extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_InsuranceProducts';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InsuranceProviderID',
        'Name',
        'Type',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'InsuranceProductsId';
    }

    public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'InsuranceProviderID', 'Id');
    }

    public function type()
    {
        return $this->belongsTo(CodeDetail::class, 'Type', 'ID');
    }

    public function policies()
    {
        return $this->hasMany(BancassurancePolicy::class, 'ProductID', 'Id');
    }
}
