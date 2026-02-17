<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollCycle extends Model
{
    protected $table = 't_HRPayrollCycles';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Year','Month','Status','OpenedOn','OpenedBy','ClosedOn','ClosedBy','ReopenedOn','ReopenedBy','Notes',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn','DeletedBy','DeletedOn',
    ];

    protected $casts = [
        'OpenedOn' => 'datetime',
        'ClosedOn' => 'datetime',
        'ReopenedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function runs()
    {
        return $this->hasMany(PayrollRun::class, 'PayrollCycleID', 'Id');
    }
}
