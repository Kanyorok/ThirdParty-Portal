<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class TrainingSessionFeedback extends Model
{
    protected $table = 't_HRTrainingSessionFeedback';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'EmployeeID',
        'RatingContent',
        'RatingTrainer',
        'RatingRelevance',
        'Comments',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn'  => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn'  => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'SessionID');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
