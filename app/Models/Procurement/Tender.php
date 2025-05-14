<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tender extends Model {
    protected $table = 't_Tenders';
    protected $primaryKey = 'TenderID';
    public $incrementing = true;

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
        'CreatedBy',
        'DateCreated',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'SubmissionDeadline' => 'datetime:Y-m-d H:i:s',
        'OpeningDate' => 'datetime:Y-m-d H:i:s',
        'DateCreated' => 'datetime:Y-m-d H:i:s',
        'ModifiedAt' => 'datetime:Y-m-d H:i:s',
    ];

    //Relationships
    public function procurementMode()
    {
        return $this->belongsTo(ProcurementMode::class, 'ProcurementModeId');
    }

    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedBy():BelongsTo {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    public function stages():HasMany {
        return $this->hasMany(TenderStage::class, 'TenderId');
    }

    public function documents(): HasMAny{
        return $this->hasMany(TenderDocument::class, 'TenderID');
    }

    public function vendors(): BelongsToMany {
        return $this->belongsToMany(Supplier::class, 't_TenderVendors', 'TenderID', 'Id')
            ->withPivot('InvitationStatus', 'ResponseDate', 'DeclineReason')
            ->withTimestamps();
    }

    public function clarifications(): HasMany {
        return $this->hasMany(VendorClarification::class, 'TenderID');
    }

    public function submissions(): HasMany {
        return $this->hasMany(TenderSubmission::class, 'TenderID');
    }

    public function scopeOpenTenders($query) {
        return $query->where('TenderType', 'Open');
    }

    public function scopeRestrictedTenders($query) {
        return $query->where('TenderType', 'Restricted');
    }

    public function scopePublished($query) {
        return $query->where('Status', 'Published');
    }

    public function isOpenTender(): bool {
        return $this->TenderType === 'Open';
    }

    public function canAcceptClarifications(): bool {
        return now()->lt($this->ClarificationDeadline);
    }
}
