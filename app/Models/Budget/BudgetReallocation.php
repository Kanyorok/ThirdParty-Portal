
<?php

namespace App\Models\Budget;

use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;

class BudgetReallocation extends Model
{
    use UserActorTrait;

    protected $table = 't_BudgetReallocations';
    public $timestamps = false;

    // Define custom timestamp column names
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = null; // No updated_at equivalent in your table

    protected $fillable = [
        'BudgetID',
        'FromBudgetLineID',
        'ToBudgetLineID',
        'FromActivityID',
        'ToActivityID',
        'BranchID',
        'DepartmentID',
        'ReallocationType',
        'Amount',
        'Justification',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ApprovedBy',
        'ApprovedOn'
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
    ];

    // Relationships
    public function budget()
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }

    public function fromLine()
    {
        return $this->belongsTo(BudgetLine::class, 'FromBudgetLineID', 'Id');
    }

    public function toLine()
    {
        return $this->belongsTo(BudgetLine::class, 'ToBudgetLineID', 'Id');
    }

    public function fromActivity()
    {
        return $this->belongsTo(BudgetActivityMaster::class, 'FromActivityID', 'Id');
    }

    public function toActivity()
    {
        return $this->belongsTo(BudgetActivityMaster::class, 'ToActivityID', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID', 'Id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'ApprovedBy', 'Id');
    }

    // Related budget limits
    public function budgetLimits()
    {
        return $this->hasMany(BudgetLineLedgerLimit::class, 'ReallocationID', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('Status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('Status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('Status', 'rejected');
    }

    public function scopeByBudget($query, $budgetId)
    {
        return $query->where('BudgetID', $budgetId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('BranchID', $branchId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('DepartmentID', $departmentId);
    }

    // Accessors
    public function getStatusBadgeClassAttribute()
    {
        return match(strtolower($this->Status)) {
            'approved' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'rejected' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->Amount, 2);
    }

    // Methods
    public function approve($userId = null)
    {
        $this->update([
            'Status' => 'approved',
            'ApprovedBy' => $userId ?? auth()->id(),
            'ApprovedOn' => now()
        ]);
    }

    public function reject()
    {
        $this->update(['Status' => 'rejected']);
    }

    public function isPending()
    {
        return strtolower($this->Status) === 'pending';
    }

    public function isApproved()
    {
        return strtolower($this->Status) === 'approved';
    }

    public function isRejected()
    {
        return strtolower($this->Status) === 'rejected';
    }
}
