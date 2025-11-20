<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;


class FuelType extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FuelTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'DeletedOn'
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
