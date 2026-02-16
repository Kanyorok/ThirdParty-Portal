<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $table = 't_HRPayrollRuns';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PayrollCycleID','Status','FinanceJournalEntryID','FinancePostingMode','FinancePostedOn','FinancePostedBy','GeneratedOn','GeneratedBy','Notes',
        'ApprovedBy','ApprovedOn','RejectedBy','RejectedOn','RejectionReason',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn',
    ];

    protected $casts = [
        'GeneratedOn' => 'datetime',
        'FinancePostedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'RejectedOn' => 'datetime',
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
