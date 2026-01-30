<?php

namespace App\Models\FleetManagement;

use App\Models\Auth\User;
use App\Models\Fleet\FleetVehicle;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetModel extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';


    protected $table = 't_FleetModels';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [

        'ModelID',
        'ModelName',
        'BrandID',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ModelId';
    }

    public function brand()
    {
        return $this->belongsTo(FleetMake::class, 'BrandID', 'Id');
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

    public function vehicles()
    {
        return $this->hasMany(FleetVehicle::class, 'Model', 'Id');
    }
}
