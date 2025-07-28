<?php

namespace App\Models\PropertyManagement;


use App\Models\Core\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyMaintenanceRequest extends Model
{
    //
    use SoftDeletes, UserActorTrait,DocumentsTrait;
    protected $table = 't_MaintenanceRequest';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequestNumber',
        'Property',
        'Block',
        'Floor',
        'Unit',
        'ReportedBy',
        'IssueType',
        'Priority',
        'IssueDescription',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
    public static function getPrimaryKey(): string
    {
        return 'PropertyMaintenanceRequestId';
    }
    public function requestId()
    {
        return $this->belongsTo(PropertyMaintenanceAssign::class, 'RequestNumber', 'Id');
    }
    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'Property', 'Id');
    }
    public function block()
    {
        return $this->belongsTo(PropertyBlock::class, 'Block', 'Id');
    }
    public function floor()
    {
        return $this->belongsTo(PropertyFloor::class, 'Floor', 'Id');
    }
    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'Unit', 'Id');
    }
    public function issueType()
    {
        return $this->belongsTo(CodeDetail::class, 'IssueType', 'ID');
    }
    public function priority()
    {
        return $this->belongsTo(CodeDetail::class, 'Priority', 'ID');
    }
}
