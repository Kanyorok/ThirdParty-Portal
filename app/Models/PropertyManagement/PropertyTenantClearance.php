<?php

namespace App\Models\PropertyManagement;

use App\Enums\Property\TenantClearanceEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyTenantClearance extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;
    protected $table = 't_TenantClearance';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseId',
        'ExitDate',
        'FinalInspection',
        'AllDuesPaid',
        'KeysReturned',
        'DepositRefunded',
        'AdditionalNotes',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];


    public static function getPrimaryKey(): string
    {
        return 'TenantClearanceId';
    }

    protected $casts = [
        'Status' => TenantClearanceEnum::class,
    ];

    public function lease()
    {
        return $this->belongsTo(PropertyNewLease::class, 'LeaseId', 'Id');
    }

    public function code()
    {
        return $this->belongsTo(CodeDetail::class, 'DepositRefunded', 'ID');
    }


}
