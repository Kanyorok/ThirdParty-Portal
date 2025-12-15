<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $table = 't_HRPayrollRuns';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PayrollCycleID','Status','GeneratedOn','GeneratedBy','Notes',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'
    ];

    protected $casts = [
        'GeneratedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function cycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'PayrollCycleID', 'Id');
    }

    public function lines()
    {
        return $this->hasMany(PayrollRunLine::class, 'PayrollRunID', 'Id');
    }
}
