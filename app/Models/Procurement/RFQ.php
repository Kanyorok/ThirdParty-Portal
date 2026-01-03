<?php

namespace App\Models\Procurement;

use App\Models\Inventory\ItemCategories;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\WorkflowPending;
use App\Models\Procurement\Requisitions;
class RFQ extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQ';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected static function boot()
    {
        parent::boot();

        static::created(function ($rfq) {
            $workflowService = app(\App\Services\Procurement\RFQ\RFQWorkflowService::class);
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user) {
                Log::info('RFQ created, submitting to workflow', [
                    'rfq_id' => $rfq->Id,
                    'rfq_number' => $rfq->RFQNumber,
                    'user_id' => $user->Id
                ]);
                
                // Use the convenience method with proper type hints
                $workflowService->submitRFQ($rfq, $user, 'RFQ Created');
            }
        });
    }

    /**
     * Get the primary key for workflow purposes
     */
    public static function getPrimaryKey(): string
    {
        return 'RFQId'; // This is the morph alias used in workflow tables
    }

    protected $fillable = [
        'RFQNumber',
        'RequisitionId',
        'Comments',
        'Status',
        'SubmissionDeadline',
        'CreatedBy',
        'ModifiedBy',
        'Remarks'
    ];

    /**
     * Relationship to RFQ Lines
     */
    public function rfqLines()
    {
        return $this->hasMany(RFQLine::class, 'RFQId');
    }

    /**
     * Relationship to Item Category
     */
    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'ItemCategoryId');
    }

    /**
     * Relationship to RFQ Responses
     */
    public function rfqResponses()
    {
        return $this->hasMany(RFQResponse::class, 'RFQId', 'Id');
    }

    /**
     * Many-to-many relationship with Suppliers
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
            ->withPivot('Status')
            ->withTimestamps();
    }

    /**
     * Relationship to Requisition
     */
    public function requisition()
    {
        return $this->belongsTo(Requisitions::class, 'RequisitionId', 'Id');
    }

    /**
     * Relationship to RFQ Sections
     */
    public function sections()
    {
        return $this->hasMany(RFQSection::class, 'RFQID', 'Id');
    }

    /**
     * Relationship to RFQ Criteria
     */
    public function criteria()
    {
        return $this->hasMany(RFQCriteria::class, 'RFQID', 'Id');
    }

    /**
     * Relationship to Committee Members
     */
    public function committeeMembers()
    {
        return $this->hasMany(RFQCommitteeMember::class, 'RFQID', 'Id');
    }

    /**
     * Relationship to Status Detail from CodeDetails
     * This joins on the Value column, not Description
     */
    public function statusDetail()
    {
        return $this->belongsTo(\App\Models\Core\Approval\CodeDetail::class, 'Status', 'Value')
            ->where('CodeId', 'RequisitionStatus')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn');
    }

    /**
     * Relationship to Workflow History
     * This allows you to fetch workflow actions for this RFQ
     */
   public function workflowHistory()
    {
        return $this->hasMany(WorkflowHistory::class, 'SourceID', 'Id')
            ->where('Source', 'RFQId')
            ->whereNull('DeletedOn');
            
    }

    
    public function workflowPending()
    {
        return $this->hasMany(WorkflowPending::class, 'SourceID', 'RFQId')
            ->where('Source', 'RFQId')
            ->whereNull('DeletedOn');
    }

    /**
     * Accessor to get human-readable status
     */
    public function getStatusDescriptionAttribute(): string
    {
        if ($this->statusDetail) {
            return $this->statusDetail->Description;
        }
        
        // Fallback mapping if relationship doesn't work
        return match(strtolower($this->Status)) {
            'ap', 'approved' => 'Approved',
            'pe', 'pending' => 'Pending',
            're', 'rejected' => 'Rejected',
            'pub', 'published' => 'Published',
            default => $this->Status ?? 'Unknown'
        };
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('Status', $status);
    }

    /**
     * Scope to filter approved RFQs
     */
    public function scopeApproved($query)
    {
        return $query->whereIn('Status', ['Ap', 'AP', 'Approved']);
    }

    /**
     * Scope to filter pending RFQs
     */
    public function scopePending($query)
    {
        return $query->whereIn('Status', ['pe', 'PE', 'Pending']);
    }

    /**
     * Check if RFQ is approved
     */
    public function isApproved(): bool
    {
        return in_array(strtolower($this->Status), ['ap', 'approved']);
    }

    /**
     * Check if RFQ is pending
     */
    public function isPending(): bool
    {
        return in_array(strtolower($this->Status), ['pe', 'pending']);
    }

    /**
     * Check if RFQ is rejected
     */
    public function isRejected(): bool
    {
        return in_array(strtolower($this->Status), ['re', 'rejected']);
    }
    // In App\Models\Procurement\RFQ.php

}