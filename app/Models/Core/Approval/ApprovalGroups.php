<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;

class ApprovalGroups extends Model
{
    // Table name
    protected $table = 't_ApprovalGroups';

    // Primary key
    protected $primaryKey = 'Id';


    public $timestamps = false;

    // Mass assignable columns
    protected $fillable = [
        'DocType',
        'ApprovalType',
        'Permission',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    // If you want to cast dates automatically
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Example: scope to only active (non-deleted) records
    public function scopeActive($query)
    {
        return $query->whereNull('DeletedOn');
    }
}
