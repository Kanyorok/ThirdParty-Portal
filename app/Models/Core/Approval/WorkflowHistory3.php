<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowHistory3 extends Model
{
    use SoftDeletes;

    protected $table = 't_WorkFlowHistory'; // Change to your actual table name if different

    protected $primaryKey = 'Id';

    public $timestamps = false; // Since the columns don't follow Laravel's default `created_at`/`updated_at`

    protected $fillable = [
        'Source',
        'SourceID',
        'Stage',
        'Amount',
        'Notes',
        'StatusId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

   protected $casts = [
    'CreatedOn' => 'datetime',
    'ModifiedOn' => 'datetime',
    'DeletedOn' => 'datetime',
    ];
}
