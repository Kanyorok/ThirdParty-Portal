<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\Model;

class PropertyMaintenanceWorkCompletion extends Model
{
    //
    protected $table = 't_WorkCompletion';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequestNumber',
        'Property',
        'Block',
        'Floor',
        'Unit',
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
}
