<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class OnboardingTask extends Model
{
    protected $table = 't_HROnboardingTasks';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'OnboardingID',
        'Title',
        'Description',
        'IsRequired',
        'DueDate',
        'Status',
        'CompletedBy',
        'CompletedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function onboarding()
    {
        return $this->belongsTo(OnboardingQueue::class, 'OnboardingID');
    }
}
