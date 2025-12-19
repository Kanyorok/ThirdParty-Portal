<?php

namespace App\Models\Procurement;

use App\Enums\TenderApprovalStatusEnum;
use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Core\Currency;
use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\ProcurementMode;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\TenderSection;
use App\Models\Procurement\TenderSupplier;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\Workflow;
use App\Models\DMS\Document;
use App\Models\Core\Approval\WorkflowPending;
use App\Traits\Model\UserActorTrait;
use App\Traits\Model\DocumentsTrait;
use Carbon\Carbon;

class Tender extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Tenders';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TenderNo',
        'Title',
        'TenderType',
        'TenderCategory',
        'ScopeOfWork',
        'Instructions',
        'SubmissionDeadline',
        'OpeningDate',
        'Status',
        'ProcurementModeId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'StartDate',
        'CurrencyId',
        'ApprovalRemarks',
        'ApprovalStatus',
        'ItemCategoryId', // Added this field
    ];

    protected $casts = [
        'TenderType' => TenderTypeEnum::class,
        'Status' => TenderStatusEnum::class,
        'ApprovalStatus' => TenderApprovalStatusEnum::class,
        'SubmissionDeadline' => 'datetime',
        'OpeningDate' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'StartDate' => 'datetime',
    ];

    // Relationships

    public function tenderCategoryRelation()
    {
        return $this->belongsTo(TenderCategory::class, 'TenderCategory', 'Id');
    }

    public function procurementMode(): BelongsTo
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 't_TenderVendors', 'TenderID', 'SupplierID')
            ->using(TenderVendor::class)
            ->withPivot('InvitationStatus', 'CreatedOn', 'ModifiedOn', 'DeletedOn');
    }

    public function itemCategoryRelation()
    {
        return $this->belongsTo(ItemCategories::class, 'ItemCategoryId', 'Id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenderInvitation::class, 'TenderId');
    }

    public function invitedSuppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 't_TenderInvitations', 'TenderId', 'SupplierId')
            ->using(TenderInvitation::class)
            ->withPivot([
                'InvitationID',
                'InvitationDate',
                'ResponseStatus',
                'ResponseDate',
                'DeclineReason',
                'ConfirmationAttachment'
            ]);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TenderStage::class, 'TenderID');
    }

    public function procurementPlan(): BelongsTo
{
    return $this->belongsTo(ConsolidatedProcurementPlan::class, 'ProcurementModeId', 'PlanID');
}
/**
 * Returns all submissions for a tender given by its tender reference.
 */
    public function submissions(): HasMany
    {
        return $this->hasMany(\App\Models\Procurement\BidSubmission::class, 'TenderRef', 'TenderNo');
    }

    public function tenderSections()
    {
        return $this->hasMany(TenderSection::class, 'TenderID', 'Id');
    }

    public function tenderSuppliers(): HasMany
    {
        return $this->hasMany(TenderSupplier::class, 'TenderID', 'Id');
    }

    /**
     * Get evaluation readiness status
     */
    public function getEvaluationReadiness()
    {
        $activeSectionsCount = TenderSection::where('TenderID', $this->Id)
            ->where('IsActive', true)
            ->count();

        if ($activeSectionsCount === 0) {
            return ['ready' => false, 'message' => 'No evaluation sections assigned'];
        }

        $totalWeight = TenderSection::where('TenderID', $this->Id)
            ->where('IsActive', true)
            ->sum('Weight');
        if (abs($totalWeight - 100) > 0.01) {
            return ['ready' => false, 'message' => "Section weights sum to {$totalWeight}%, should be 100%"];
        }

        $responsiveBids = $this->submissions()
            ->where('BidStatus', 'responsive')
            ->where('IsResponsive', true)
            ->count();

        if ($responsiveBids === 0) {
            return ['ready' => false, 'message' => 'No responsive bids available for evaluation'];
        }

        return [
            'ready' => true,
            'message' => "Ready: {$responsiveBids} responsive bid(s), {$this->tenderSections->count()} section(s)",
            'responsive_bids' => $responsiveBids,
            'sections_count' => $this->tenderSections->count()
        ];
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    // Scopes
    public function scopeActiveTenders($query)
    {
        return $query->where('Status', TenderStatusEnum::Published->value)
            ->whereDate('SubmissionDeadline', '>=', Carbon::now()->toDateString());
    }

    public function scopeClosedTenders($query)
    {
        return $query->where('Status', TenderStatusEnum::Closed->value);
    }

    public function scopeForVendor($query, $Id)
    {
        return $query->where(function ($q) use ($Id) {
            $q->where('TenderType', TenderTypeEnum::Open->value)
                ->orWhereHas('suppliers', fn($q) => $q->where('Id', $Id));
        });
    }

    public function isOpen(): bool
    {
        return $this->TenderType === TenderTypeEnum::Open;
    }

    public function canAcceptSubmissions(): bool
    {
        if ($this->Status !== TenderStatusEnum::Published) {
            return false;
        }
        if (!$this->SubmissionDeadline) {
            return true;
        }
        return Carbon::now()->lte(Carbon::parse($this->SubmissionDeadline)->endOfDay());
    }

    // New helper methods
    public function isRestricted(): bool
    {
        return $this->TenderType === TenderTypeEnum::Restricted;
    }

    public function isPublished(): bool
    {
        return $this->Status === TenderStatusEnum::Published;
    }

    public function isClosed(): bool
    {
        return $this->Status === TenderStatusEnum::Closed;
    }

    public function hasSupplier(Supplier $supplier): bool
    {
        return $this->suppliers()->where('Id', $supplier->Id)->exists();
    }

    public function tenderCategory()
    {
        return $this->belongsTo(TenderCategory::class, 'TenderCategory');
    }

    public function tenderItems()
    {
        return $this->hasMany(TenderItems::class, 'TenderID', 'Id');
    }

    public function items()
    {
        return $this->hasMany(TenderItems::class, 'TenderID', 'Id');
    }

    /**
     * Get the primary key for workflow (morph alias)
     * This returns the string identifier for the workflow system
     */
    public static function getPrimaryKey(): string
    {
        return 'tender'; // This is the morph alias for workflow, not the database column
    }
    // public function workflows()
    // {
    //     return $this->morphMany(
    //         Workflow::class,
    //         'source',
    //         'Source',   // morph type column
    //         'SourceID'  // morph id column
    //     );
    // }

    /**
     * Keep your existing route key name to avoid breaking other modules
     */
    public function getRouteKeyName(): string
    {
        return 'TenderID'; // Keep this as is for your routes
    }

    /**
     * Workflow history relationship - FIXED
     * The morphMany relationship should use 'source' as the method name in WorkflowHistory
     */
   public function workflowHistory()
    {
        return $this->hasMany(WorkflowHistory::class, 'SourceID', 'PlanID')
            ->where('Source', $this->getTable())
            ->whereNull('DeletedOn');
            
    }

    
    public function workflowPending()
    {
        return $this->hasMany(WorkflowPending::class, 'SourceID', 'PlanID')
            ->where('Source', $this->getTable())
            ->whereNull('DeletedOn');
    }


    /**
     * Get all awards for this tender (HasMany relationship)
     */
    public function awards(): HasMany
    {
        return $this->hasMany(TenderAward::class, 'TenderID', 'Id');
    }

    /**
     * Get the single award for this tender (HasOne relationship)
     */
    public function award(): HasOne
    {
        return $this->hasOne(TenderAward::class, 'TenderID', 'Id');
    }

     /**
     * Check if tender is approved
     */
    public function isApproved(): bool
    {
        return $this->ApprovalStatus === TenderApprovalStatusEnum::APPROVED;
    }

     /**
     * Check if tender is rejected
     */
    public function isRejected(): bool
    {
        return $this->ApprovalStatus === TenderApprovalStatusEnum::REJECTED;
    }

    /**
     * Get approval status badge color
     */
    public function getApprovalStatusBadgeAttribute(): string
    {
        return match($this->ApprovalStatus) {
            TenderApprovalStatusEnum::PENDING => 'warning',
            TenderApprovalStatusEnum::APPROVED => 'success',
            TenderApprovalStatusEnum::REJECTED => 'danger',
            default => 'secondary',
        };
    }

}