<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyNewTenant extends Model
{
    //
    protected $table = 't_AddTenants';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TenantType',
        'TenantName',
        'IDRegistrationNo',
        'PhoneNumber',
        'EmailAddress',
        'Nationality',
        'PostalAddress',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
}
