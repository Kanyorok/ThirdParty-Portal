<?php

namespace App\Models\ThirdParty;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierMaster extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    public const string DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierMaster';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ThirdPartyId',
        'SupplierID',
        'ApprovalStatus',
        'IsPrequalified',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'IsPrequalified' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'ApprovalStatus' => \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'SupplierMasterId';
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id'
        );
    }

    public function suppliers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ThirdParies\Supplier::class, 'SupplierMasterId', 'Id');
    }

    public function workflowHistory(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(
            \App\Models\Core\Approval\WorkflowHistory::class,
            'source',
            'Source',
            'SourceID',
            'Id'
        );
    }

    public function prequalificationApplications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Procurement\Prequalification\PrequalificationApplication::class, 'SupplierID', 'Id');
    }

    // Note: ApprovalStatus is a string enum (e.g., 'P', 'A', 'R'), not a foreign key
    // Commenting out incorrect relationship to prevent SQL errors
}
