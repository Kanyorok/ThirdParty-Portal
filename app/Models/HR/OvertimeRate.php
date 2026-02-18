<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class OvertimeRate extends Model
{
    protected $table = 't_HROvertimeRates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'GradeID',
        'RateMultiplier',
        'EffectiveFrom',
        'EffectiveTo',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'RateMultiplier' => 'decimal:4',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function grade()
    {
        return $this->belongsTo(JobGrade::class, 'GradeID');
    }
}
