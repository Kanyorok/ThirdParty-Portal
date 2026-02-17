<?php

namespace App\Models\CRM\Training;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class TrainingTrainer extends Model
{
    protected $table = 't_CRMTrainingTrainers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'TrainerType',
        'UserID',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }
}
