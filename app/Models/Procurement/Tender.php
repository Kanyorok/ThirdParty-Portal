<?php

namespace App\Models\Procurement;

use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Models\Auth\User;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tender extends Model
{
    use SoftDeletes;

    protected $table = 't_Tenders';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'RelatedPRID',
        'ProcurementModeId',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'TenderType' => TenderTypeEnum::class,
        'TenderCategory' => TenderCategoryEnum::class,
        'Status' => TenderStatusEnum::class,
        'SubmissionDeadline' => 'date:Y-m-d',
        'OpeningDate' => 'date:Y-m-d',
        'CreatedOn' => 'datetime',
    ];

    // Relationships

    public function procurementMode(): BelongsTo
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId', 'Id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenderInvitation::class, 'TenderID');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TenderStage::class, 'Id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 't_TenderVendors', 'TenderID', 'Id')
            ->using(TenderVendor::class)
            ->withPivot('InvitationStatus', 'InvitationDate', 'ResponseDate');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenderDocument::class, 'TenderID');
    }

    public function clarifications(): HasMany
    {
        return $this->hasMany(VendorClarifications::class, 'TenderID');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }


    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    public function scopeActive($query)
    {
        return $query->where('Status', TenderStatusEnum::Published->value)
            ->where('SubmissionDeadline', '>=', now()->toDateString());
    }

    // Scopes

    public function scopeClosed($query)
    {
        return $query->where('Status', TenderStatusEnum::Closed->value);
    }

    public function scopeForVendor($query, $Id)
    {
        return $query->where(function ($q) use ($Id) {
            $q->where('TenderType', TenderTypeEnum::Open->value)
                ->orWhereHas('vendors', fn($q) => $q->where('Id', $Id));
        }); //TODO; Work on the vendor model
    }

    public function isOpen(): bool
    {
        return $this->TenderType === TenderTypeEnum::Open;
    }

    public function canAcceptSubmissions(): bool
    {
        return $this->Status === TenderStatusEnum::Published &&
            now()->lessThan($this->SubmissionDeadline);
    }

    public function acceptedSubmissions(): HasMany
    {
        return $this->submissions()->where(
            'Status',
            SubmissionStatusEnum::Submitted
        );
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TenderSubmission::class, 'TenderID');
    }

    public function publish(): void
    {
        if (!$this->Status->canTransitionTo(TenderStatusEnum::Published)) {
            throw new InvalidArgumentException('Tender cannot be published from current status');
        }

        $this->update(['Status' => TenderStatusEnum::Published]);

        if ($this->TenderType === TenderTypeEnum::Restricted) {
            event(new RestrictedTenderPublished($this));
        } else {
            event(new OpenTenderPublished($this));
        }
    }

    public function isDraft(): bool
    {
        return $this->Status === \App\Enums\TenderStatusEnum::Draft;
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function isDeletable(): bool
    {
        return $this->isDraft();
    }
}
