<?php

namespace App\Models\HR\Exit;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitInterview extends Model
{
    protected $table = 't_HRExitInterviews';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'InterviewerID',
        'InterviewDate',
        'Mode',
        'AttritionReason',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'InterviewDate' => 'date',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'InterviewerID');
    }
}
