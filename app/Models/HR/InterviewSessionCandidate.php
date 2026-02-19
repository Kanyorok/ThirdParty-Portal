<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class InterviewSessionCandidate extends Model
{
    protected $table = 't_HRInterviewSessionCandidates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'ApplicationID',
        'SlotTime',
        'Status',
        'Score',
        'Recommendation',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'SlotTime' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(InterviewSession::class, 'SessionID');
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }

    public function scores()
    {
        return $this->hasMany(InterviewSessionScore::class, 'SessionCandidateID');
    }
}
