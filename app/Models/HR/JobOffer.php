<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    protected $table = 't_HRJobOffers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ApplicationID',
        'OfferDate',
        'SalaryOffered',
        'Benefits',
        'Status',
        'ApprovedOn',
        'ApprovedBy',
        'SentOn',
        'AcceptedOn',
        'RejectedOn',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }

    public function onboarding()
    {
        return $this->hasOne(OnboardingQueue::class, 'OfferID', 'Id');
    }
}
