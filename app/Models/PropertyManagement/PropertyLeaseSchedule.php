<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyLeaseSchedule extends Model
{
    protected $table = 't_ScheduleLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'leaseID',
        'PaymentFrequency',
        'StartDate',
        'EndDate',
        'BaseRent',
        'ServiceCharge',
        'ParkingFee',
        'OtherCharges',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
