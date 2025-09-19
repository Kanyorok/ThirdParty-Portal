<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\DMS\Document;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidSubmission extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_BidSubmissions';

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    protected $fillable = [
        'TenderRef',
        'SupplierName', 
        'SupplierId',
        'SubmissionMode',
        'ReceivedAt',
        'RecordedBy',
        'Remarks',
        'EncryptedDocuments', 
        'EncryptionKey',
        'SubmissionSource', // 'manual' or 'portal'
        'DocumentsAccessible', // Controls if documents can be viewed before ceremony
        'BidOpeningDate',
        
        // Business fields for structured bids
        'BidAmount',
        'Currency',
        'ValidityPeriod',
        'DeliveryPeriod', 
        'PaymentTerms',
        'BidStatus',
        
        // Evaluation fields
        'TechnicalScore',
        'FinancialScore',
        'TotalScore',
        'IsResponsive',
        'ResponsivenessRemarks',
        'EvaluationNotes',
        'OpenedAt',
        'OpenedBy',
        
        // Opening ceremony fields
        'CeremonyType',
        'CeremonyNotes',
        'OfficersPresent',
        'ReadOutSummary',
        'BidSecurityPresent',
        'ReceivedOnTime',
        
        // Enhanced responsiveness fields (aligned with t_BidResponsiveness)
        'SubmittedTimely',
        'HasMandatoryDocuments',
        'IsEligible',
        'TimelySubmissionRemarks',
        'DocumentComplianceRemarks',
        'EligibilityRemarks',
        'ResponsivenessCheckedAt',
        'ResponsivenessCheckedBy',
        'TenderSupplierID',
        
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy', 
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'ReceivedAt' => 'datetime',
        'BidOpeningDate' => 'datetime',
        'DocumentsAccessible' => 'boolean',
        'OpenedAt' => 'datetime',
        'BidAmount' => 'decimal:2',
        'TechnicalScore' => 'decimal:2',
        'FinancialScore' => 'decimal:2', 
        'TotalScore' => 'decimal:2',
        'IsResponsive' => 'boolean',
        'ValidityPeriod' => 'integer',
        'DeliveryPeriod' => 'integer',
        'BidSecurityPresent' => 'boolean',
        'ReceivedOnTime' => 'boolean',
        'SubmittedTimely' => 'boolean',
        'HasMandatoryDocuments' => 'boolean',
        'IsEligible' => 'boolean',
        'ResponsivenessCheckedAt' => 'datetime',
    ];

    // Relationships
    public function submissionMode()
    {
        return $this->belongsTo(CodeDetail::class, 'SubmissionMode', 'ID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\ThirdParies\Supplier::class, 'SupplierId', 'Id');
    }

    public function openedByUser()
    {
        return $this->belongsTo(User::class, 'OpenedBy', 'Id');
    }

    public function tender()
    {
        return $this->belongsTo(\App\Models\Procurement\Tender::class, 'TenderRef', 'TenderNo');
    }

    public function responsivenessCheckedByUser()
    {
        return $this->belongsTo(User::class, 'ResponsivenessCheckedBy', 'Id');
    }

    // Document encryption and access control methods
    public function canAccessDocuments(): bool
    {
        return $this->DocumentsAccessible || $this->isBidOpeningCeremonyStarted();
    }

    public function isBidOpeningCeremonyStarted(): bool
    {
        return $this->BidOpeningDate && now()->gte($this->BidOpeningDate);
    }

    public function isFromPortal(): bool
    {
        return $this->SubmissionSource === 'portal';
    }

    // Status and evaluation helper methods
    public function isDraft(): bool
    {
        return $this->BidStatus === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->BidStatus === 'submitted';
    }

    public function isOpened(): bool
    {
        return !is_null($this->OpenedAt);
    }

    public function isEvaluated(): bool
    {
        return $this->BidStatus === 'evaluated';
    }

    public function isResponsive(): bool
    {
        return $this->IsResponsive === true;
    }

    public function isAwarded(): bool
    {
        return $this->BidStatus === 'awarded';
    }

    public function isPendingEvaluation(): bool
    {
        return $this->BidStatus === 'responsive' && $this->IsResponsive === true;
    }

    public function isEvaluationInProgress(): bool
    {
        return $this->BidStatus === 'responsive' && !is_null($this->TechnicalScore);
    }

    public function isEvaluationComplete(): bool
    {
        return $this->BidStatus === 'evaluated' && 
               !is_null($this->TechnicalScore) && 
               !is_null($this->FinancialScore) && 
               !is_null($this->TotalScore);
    }

    /**
     * Get evaluation status for display
     */
    public function getEvaluationStatus(): array
    {
        if ($this->BidStatus === 'non-responsive') {
            return [
                'status' => 'non-responsive',
                'label' => 'Non-Responsive',
                'badge_class' => 'bg-danger',
                'icon' => 'fas fa-times-circle'
            ];
        }

        if ($this->BidStatus === 'submitted' || !$this->IsResponsive) {
            return [
                'status' => 'pending-responsiveness',
                'label' => 'Pending Responsiveness Check',
                'badge_class' => 'bg-warning',
                'icon' => 'fas fa-clock'
            ];
        }

        if ($this->isPendingEvaluation()) {
            return [
                'status' => 'pending-evaluation',
                'label' => 'Ready for Evaluation',
                'badge_class' => 'bg-info',
                'icon' => 'fas fa-clipboard-check'
            ];
        }

        if ($this->isEvaluationInProgress()) {
            return [
                'status' => 'evaluation-in-progress',
                'label' => 'Evaluation in Progress',
                'badge_class' => 'bg-primary',
                'icon' => 'fas fa-spinner'
            ];
        }

        if ($this->isEvaluationComplete()) {
            return [
                'status' => 'evaluation-complete',
                'label' => 'Evaluation Complete',
                'badge_class' => 'bg-success',
                'icon' => 'fas fa-check-circle'
            ];
        }

        if ($this->isAwarded()) {
            return [
                'status' => 'awarded',
                'label' => 'Awarded',
                'badge_class' => 'bg-success',
                'icon' => 'fas fa-trophy'
            ];
        }

        return [
            'status' => 'unknown',
            'label' => 'Unknown Status',
            'badge_class' => 'bg-secondary',
            'icon' => 'fas fa-question'
        ];
    }

    public function markAsOpened(User $openedBy, array $ceremonyDetails = []): void
    {
        $updateData = [
            'OpenedAt' => now(),
            'OpenedBy' => $openedBy->Id,
            'DocumentsAccessible' => true,
            'BidStatus' => $this->BidStatus === 'draft' ? 'submitted' : $this->BidStatus
        ];
        
        // Add ceremony details if provided
        if (isset($ceremonyDetails['ceremony_type'])) {
            $updateData['CeremonyType'] = $ceremonyDetails['ceremony_type'];
        }
        if (isset($ceremonyDetails['ceremony_notes'])) {
            $updateData['CeremonyNotes'] = $ceremonyDetails['ceremony_notes'];
        }
        if (isset($ceremonyDetails['officers_present'])) {
            $updateData['OfficersPresent'] = $ceremonyDetails['officers_present'];
        }
        if (isset($ceremonyDetails['read_out_summary'])) {
            $updateData['ReadOutSummary'] = $ceremonyDetails['read_out_summary'];
        }
        if (isset($ceremonyDetails['bid_security_present'])) {
            $updateData['BidSecurityPresent'] = $ceremonyDetails['bid_security_present'];
        }
        if (isset($ceremonyDetails['received_on_time'])) {
            $updateData['ReceivedOnTime'] = $ceremonyDetails['received_on_time'];
        }
        
        $this->update($updateData);
    }

    public function markAsResponsive(string $remarks = null): void
    {
        $this->update([
            'IsResponsive' => true,
            'BidStatus' => 'responsive',
            'ResponsivenessRemarks' => $remarks,
            'ResponsivenessCheckedAt' => now(),
            'ResponsivenessCheckedBy' => auth()->id()
        ]);
    }

    public function markAsNonResponsive(string $remarks): void
    {
        $this->update([
            'IsResponsive' => false,
            'BidStatus' => 'non-responsive',
            'ResponsivenessRemarks' => $remarks,
            'ResponsivenessCheckedAt' => now(),
            'ResponsivenessCheckedBy' => auth()->id()
        ]);
    }

    /**
     * Perform detailed responsiveness check with all criteria
     */
    public function performDetailedResponsivenessCheck(array $criteria, string $overallRemarks = null, ?User $checkedBy = null): void
    {
        $checkedBy = $checkedBy ?? auth()->user();
        
        // Determine overall responsiveness based on all criteria
        $isResponsive = ($criteria['submitted_timely'] ?? true) &&
                        ($criteria['has_mandatory_documents'] ?? true) &&
                        ($criteria['is_eligible'] ?? true);
        
        $this->update([
            'SubmittedTimely' => $criteria['submitted_timely'] ?? null,
            'HasMandatoryDocuments' => $criteria['has_mandatory_documents'] ?? null,
            'IsEligible' => $criteria['is_eligible'] ?? null,
            'TimelySubmissionRemarks' => $criteria['timely_remarks'] ?? null,
            'DocumentComplianceRemarks' => $criteria['document_remarks'] ?? null,
            'EligibilityRemarks' => $criteria['eligibility_remarks'] ?? null,
            'IsResponsive' => $isResponsive,
            'BidStatus' => $isResponsive ? 'responsive' : 'non-responsive',
            'ResponsivenessRemarks' => $overallRemarks,
            'ResponsivenessCheckedAt' => now(),
            'ResponsivenessCheckedBy' => $checkedBy->Id
        ]);
    }

    /**
     * Get responsiveness check summary
     */
    public function getResponsivenessSummary(): array
    {
        return [
            'submitted_timely' => [
                'status' => $this->SubmittedTimely,
                'remarks' => $this->TimelySubmissionRemarks
            ],
            'has_mandatory_documents' => [
                'status' => $this->HasMandatoryDocuments,
                'remarks' => $this->DocumentComplianceRemarks
            ],
            'is_eligible' => [
                'status' => $this->IsEligible,
                'remarks' => $this->EligibilityRemarks
            ],
            'overall_responsive' => $this->IsResponsive,
            'overall_remarks' => $this->ResponsivenessRemarks,
            'checked_at' => $this->ResponsivenessCheckedAt,
            'checked_by' => $this->responsivenessCheckedByUser?->name ?? 'Unknown'
        ];
    }

    public function updateScores(float $technical, float $financial, string $notes = null): void
    {
        $total = $technical + $financial;
        $this->update([
            'TechnicalScore' => $technical,
            'FinancialScore' => $financial,
            'TotalScore' => $total,
            'BidStatus' => 'evaluated',
            'EvaluationNotes' => $notes
        ]);
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('BidStatus', 'submitted');
    }

    public function scopeOpened($query) 
    {
        return $query->whereNotNull('OpenedAt');
    }

    public function scopeResponsive($query)
    {
        return $query->where('IsResponsive', true);
    }

    public function scopeEvaluated($query)
    {
        return $query->where('BidStatus', 'evaluated');
    }

    public function scopeForTender($query, $tenderRef)
    {
        return $query->where('TenderRef', $tenderRef);
    }

    public function isManualSubmission(): bool
    {
        return $this->SubmissionSource === 'manual';
    }

    public function getStatusAttribute(): string
    {
        if (!$this->canAccessDocuments()) {
            return 'sealed';
        }
        return $this->isBidOpeningCeremonyStarted() ? 'opened' : 'accessible';
    }

    // TODO: Document Management Integration
    // Will be implemented once DMS schema compatibility is resolved
}
