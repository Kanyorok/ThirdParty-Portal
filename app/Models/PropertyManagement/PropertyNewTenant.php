<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\Model;

class PropertyNewTenant extends Model
{
    //
    protected $table = 't_TenantMaintenance';
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
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'TenantMaintenanceId';
    }

    public function type()
    {
        return $this->belongsTo(CodeDetail::class, 'TenantType', 'ID');
    }
}
