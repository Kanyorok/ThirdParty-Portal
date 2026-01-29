<?php

namespace App\Models\Fleet;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelType extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_FuelTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FuelName',
        'IsActive',
        'FuelTypeCode',
        'Description',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FuelId';
    }

    public function vehicles()
    {
        return $this->hasMany(FleetVehicle::class, 'FuelType', 'Id');
    }
}
