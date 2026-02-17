<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 't_HRApplicants';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'FirstName',
        'LastName',
        'OtherNames',
        'Email',
        'Phone',
        'Gender',
        'DateOfBirth',
        'Address',
        'Source',
        'LinkedInUrl',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'ApplicantID', 'Id');
    }
}
