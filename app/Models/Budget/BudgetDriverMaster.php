<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriverMaster extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_BudgetDriversMaster';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriversMasterId';
    }

    protected $fillable = [
        'DriverName',
        //'DriverCode',
        'DriverTypeID',
        //'UOMID',
        'IsActive',
        'Frequency',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',

        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function driverType()
    {
        return $this->belongsTo(BudgetDriver::class, 'DriverTypeID', 'Id');
    }
}
