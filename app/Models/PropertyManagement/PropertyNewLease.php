<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyNewLease extends Model
{
    //
    protected $table = 't_AddLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Tenant',
        'PropertyID',
        'BlockID',
        'FloorID',
        'Unit',
        'StartDate',
        'EndDate',
        'PaymentFrequency',
        'MonthlyRent',
        'Deposit',
        'DueDay',
        'SpecialTerms',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
