<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Fleet\FleetTripLog;
use App\Models\Core\CodeDetail;
use App\Models\ThirdParies\Supplier;


class ContractedDriver extends Model
{

    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ContractedDrivers';
    protected $primaryKey = 'Id';
    public $timestamps = false;


    protected $fillable = [
        'DriverNo',
        'FullName',
        'NationalID',
        'Phone',
        'Company',
        'ContractStartDate',
        'ContractEndDate',
        'IsActive',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DriverId';
    }
    
    public function company()
    {
        return $this->belongsTo(Supplier::class, 'Company', 'Id');
    }
    
}
