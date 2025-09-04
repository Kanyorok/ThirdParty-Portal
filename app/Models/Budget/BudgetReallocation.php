<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetReallocation extends Model
{
    protected $table = 't_BudgetReallocations';
    public $timestamps = false;

    protected $fillable = [
        'BudgetID','FromBudgetLineID','ToBudgetLineID','FromActivityID','ToActivityID',
        'BranchID','DepartmentID','ReallocationType','Amount','Justification',
        'Status','CreatedBy','CreatedOn','ApprovedBy','ApprovedOn'
    ];

    public function budget() { return $this->belongsTo(Budget::class, 'BudgetID'); }
    public function fromLine() { return $this->belongsTo(BudgetLine::class, 'FromBudgetLineID'); }
    public function toLine() { return $this->belongsTo(BudgetLine::class, 'ToBudgetLineID'); }
}