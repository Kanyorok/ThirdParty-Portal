<?php

namespace App\Models\PropertyManagement;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyMaintenanceWorkCompletion extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;
    //
    protected $table = 't_WorkCompletion';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequestNumber',
        'CompletionDate',
        'WorkDoneSummary',
        'PartsUsed',
        'Cost',
        'FinalStatus',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
    public static function getPrimaryKey(): string
    {
        return 'WorkCompletionId';
    }
    public function request()
    {
        return $this->belongsTo(PropertyMaintenanceAssign::class, 'RequestNumber', 'Id');
    }
        public function finalstatus()
    {
        return $this->belongsTo(CodeDetail::class, 'FinalStatus', 'ID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }
}
