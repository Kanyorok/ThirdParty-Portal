<?php

namespace App\Models\FleetManagement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\CodeDetail;
use App\Models\Auth\User;

class VehicleRegistry extends Model
{
    use UserActorTrait, SoftDeletes;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $table = 't_Vehicles';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';

    protected $fillable = [

        'RegistrationNo',
        'Model',
        'Year',
        'Color',
        'ChassisNo',
        'Type',
        'Make',
        'EngineNo',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];


    public static function getPrimaryKey(): string
    {
        return 'VehicleId';
    }

    public function brand()
    {
        return $this->belongsTo(FleetMake::class, 'Make', 'Id');
    }

    public function model()
    {
        return $this->belongsTo(FleetModel::class, 'Model', 'Id');
    }

    public function vehicleType ()
    {
        return $this->belongsTo(CodeDetail::class, 'Type', 'ID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }


}
