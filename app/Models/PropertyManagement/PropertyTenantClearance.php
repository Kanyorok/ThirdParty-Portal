<?php

namespace App\Models\PropertyManagement;

use App\Http\Controllers\Property\PropertyNewLeaseController;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyTenantClearance extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_TenantClearance';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Tenant',
        'ExitDate',
        'FinalInspection',
        'AllDuesPaid',
        'KeysReturned',
        'DepositRefunded',
        'AdditionalNotes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    
    public static function getPrimaryKey(): string
    {
        return 'TenantClearanceId';
    }

    public function tenant()
    {
        return $this->belongsTo(PropertyNewTenant::class,'Tenant','Id');
    }
    public function code()
    {
        return $this->belongsTo(CodeDetail::class,'DepositRefunded','ID');
    }
    

}
