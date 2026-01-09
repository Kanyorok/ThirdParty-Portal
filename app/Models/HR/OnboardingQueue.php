<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class OnboardingQueue extends Model
{
    protected $table = 't_HROnboardingQueues';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'OfferID',
        'ApplicationID',
        'EmployeeID',
        'CandidateName',
        'Status',
        'StartDate',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function offer()
    {
        return $this->belongsTo(JobOffer::class, 'OfferID');
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function tasks()
    {
        return $this->hasMany(OnboardingTask::class, 'OnboardingID', 'Id');
    }
}
