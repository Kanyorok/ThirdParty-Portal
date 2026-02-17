<?php

namespace App\Models\CRM\Training;

use Illuminate\Database\Eloquent\Model;

class TrainingProgramTarget extends Model
{
    protected $table = 't_CRMTrainingProgramTargets';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ProgramID',
        'TargetType',
        'TargetID',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(TrainingProgram::class, 'ProgramID');
    }
}
