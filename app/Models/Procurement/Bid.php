<?php

namespace App\Models\Procurement;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Currency;
use App\Models\DMS\Document;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bid extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Bids';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TenderId',
        'SupplierId',
        'BidAmount',
        'Currency',
        'ValidityPeriod',
        'DeliveryPeriod',
        'PaymentTerms',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'BidAmount' => 'decimal:2',
        'ValidityPeriod' => 'integer',
        'DeliveryPeriod' => 'integer',
        'Status' => 'string',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Relationships
    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'TenderId', 'Id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'SupplierId', 'Id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'Currency', 'Code');
    }

    // Scopes
    public function scopeDrafts($query)
    {
        return $query->where('Status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('Status', 'submitted');
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->Status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->Status === 'submitted';
    }

    // Document Management Integration
    public function newDocument($module, $file, $permissions, $actor)
    {
        // Uses the same document management pattern as InvoiceEntry
        // This will be called similar to how InvoiceEntryController does it
        return $this->morphMany(\App\Models\DMS\Document::class, 'documentable')
            ->create([
                'module' => $module,
                'file_path' => $file->store('bid-documents'),
                'original_filename' => $file->getClientOriginalName(),
                'permissions' => $permissions,
                'uploaded_by' => $actor->Id,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }
}
