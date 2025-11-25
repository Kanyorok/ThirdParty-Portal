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
        //'RelatedPRID',
        'ProcurementModeId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'StartDate',
        'CurrencyId',
        // 'TenderCategory',
        'ApprovalRemarks',
        'ApprovalStatus', // 1 for approved, 2 for rejected, 0 for pending
    ];

    protected $casts = [
        'TenderType' => TenderTypeEnum::class,
        'Status' => TenderStatusEnum::class,
        'ApprovalStatus' => TenderApprovalStatusEnum::class,
        // 'TenderCategory' => TenderCategoryEnum::class,
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

    //TODO: with tenderinvitations
    // public function suppliers(): BelongsToMany
    // {
    //     return $this->belongsToMany(Supplier::class, 't_TenderVendors', 'TenderID', 'SupplierID')
    //         ->using(TenderVendor::class)
    //         ->withPivot('InvitationStatus', 'InvitationDate', 'ResponseDate')
    //         ->withTimestamps('CreatedOn', 'ModifiedOn', 'DeletedOn');
    // }

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
        return $this->belongsTo(ProcurementPlan::class, 'RelatedPRID');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenderDocument::class, 'TenderID', 'Id');
    }

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
        // Use DB-backed checks to avoid false negatives from lazy or filtered relations
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

    // public function creator(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'CreatedBy');
    // }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    // Scopes
    public function scopeActiveTenders($query)
    {
        // Active if published and deadline is today or later (inclusive day)
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

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'TenderID';
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
}
