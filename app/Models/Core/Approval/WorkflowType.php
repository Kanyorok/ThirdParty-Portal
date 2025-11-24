<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;

class WorkflowType extends Model
{
    protected $table = 't_WorkFlowTypes';

    protected $primaryKey = 'Id'; // Custom PK

    public $timestamps = false; // Not using Laravel's default timestamps

    protected $fillable = [
        'TypeID',
        'Name',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    // Add casts if needed
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Optional: Soft delete-style check
    public function isDeleted(): bool
    {
        return !is_null($this->DeletedOn);
    }
}
