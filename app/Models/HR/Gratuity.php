<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Gratuity extends Model
{
    protected $table = 't_HRGratuities';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'Year',
        'RatePercent',
        'GrossPay',
        'Amount',
        'Status',
        'PaidOn',
        'PaidBy',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'RatePercent' => 'decimal:2',
        'GrossPay' => 'decimal:2',
        'Amount' => 'decimal:2',
        'PaidOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
