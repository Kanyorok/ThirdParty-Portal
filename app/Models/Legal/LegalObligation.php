<?php

namespace App\Models\Legal;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalObligation extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalObligations';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'Title',
        'SourceType', // 'Contract' or 'Case'
        'DueDate',
        'ExpiryDate',
        'Status', // Pending, Completed, Overdue
        'Description',
        'AssignedTo', // User ID for assignment
        'CreatedBy',
        'ModifiedBy',
        'IsActive',
        'ScheduledID', // Foreign key to t_Schedule
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalObligationsId';
    }

    public function details()
    {
        return $this->belongsTo(CodeDetail::class, 'SourceType');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'AssignedTo', 'Id');
    }
}
