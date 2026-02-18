<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobApplicationScreening extends Model
{
    protected $table = 't_HRJobApplicationScreenings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ApplicationID',
        'Status',
        'ApprovalStatus',
        'ApprovedBy',
        'ApprovedOn',
        'Score',
        'Notes',
        'ScreenedBy',
        'ScreenedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }
}
