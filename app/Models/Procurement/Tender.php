<?php

namespace App\Models\Procurement;

use App\Enums\TenderCategoryEnum;
use App\Enums\TenderStatusEnum;
use App\Enums\TenderTypeEnum;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Tender extends Model
{
    protected $table = 't_Tenders';
    protected $primaryKey = 'Id';
    protected $keyType = 'integer';

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
        'DateCreated',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'TenderType' => TenderTypeEnum::class,
        'Status' => TenderStatusEnum::class,
        'TenderCategory' => TenderCategoryEnum::class,
        'SubmissionDeadline' => 'date:Y-m-d',
        'OpeningDate' => 'date:Y-m-d',
        'DateCreated' => 'datetime',
    ];

    // Relationships

    public function acceptedInvitations(): HasMany
    {
        return $this->invitations()->accepted;
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenderInvitation::class, 'TenderID');
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
        return $this->hasMany(VendorClarification::class, 'TenderID');
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
            'Status', SubmissionStatusEnum::Submitted
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
}
