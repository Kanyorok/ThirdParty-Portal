<?php

namespace App\Models\FleetManagement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\FleetManagement\FleetMake;


class FleetModel extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $table = 't_FleetModels';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [

        'ModelID',
        'ModelName',
        'BrandID',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
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


}
