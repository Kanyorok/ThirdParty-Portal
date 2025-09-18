<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
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
        'Documents',
        'EncryptedDocuments', 
        'EncryptionKey',
        'SubmissionSource', // 'manual' or 'portal'
        'DocumentsAccessible', // Controls if documents can be viewed before ceremony
        'BidOpeningDate',
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
}
