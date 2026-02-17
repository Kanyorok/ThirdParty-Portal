<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class TrainingTrainer extends Model
{
    protected $table = 't_HRTrainingTrainers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'TrainerType',
        'EmployeeID',
        'Name',
        'Email',
        'Phone',
        'Expertise',
        'Certifications',
        'Rate',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Rate' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
