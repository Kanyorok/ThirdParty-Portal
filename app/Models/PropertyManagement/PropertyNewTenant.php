<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CodeDetail;
use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
class PropertyNewTenant extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;
    protected $table = 't_TenantMaintenance';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ThirdPartyId',
        'TenantType',
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

        public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

}
