<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollDeduction extends Model
{
    protected $table = 't_HRPayrollDeductions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'IsMandatory',
        'ShowInPayslip',
        'ApplyFor',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'IsMandatory' => 'boolean',
        'ShowInPayslip' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function rules()
    {
        return $this->hasMany(PayrollDeductionRule::class, 'DeductionID');
    }
}
