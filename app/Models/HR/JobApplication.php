<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 't_HRJobApplications';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'JobOpeningID',
        'ApplicantID',
        'AppliedOn',
        'Status',
        'ExpectedSalary',
        'NoticePeriodDays',
        'ResumePath',
        'CoverLetterPath',
        'Source',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function opening()
    {
        return $this->belongsTo(JobOpening::class, 'JobOpeningID');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'ApplicantID');
    }

    public function documents()
    {
        return $this->hasMany(JobApplicationDocument::class, 'ApplicationID', 'Id');
    }

    public function screenings()
    {
        return $this->hasMany(JobApplicationScreening::class, 'ApplicationID', 'Id');
    }

    public function interviewCandidates()
    {
        return $this->hasMany(InterviewSessionCandidate::class, 'ApplicationID', 'Id');
    }

    public function offers()
    {
        return $this->hasMany(JobOffer::class, 'ApplicationID', 'Id');
    }
}
