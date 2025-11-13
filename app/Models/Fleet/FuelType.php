<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelType extends Model
{
    use SoftDeletes;

    protected $table = 't_FuelTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    // Tell Laravel to use your custom DeletedOn column
    const DELETED_AT = 'DeletedOn';
    protected $dates = ['DeletedOn'];

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

    public function vehicles()
    {
        return $this->hasMany(FleetVehicle::class, 'FuelType', 'Id');
    }
}
