<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\ImageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\BusinessTypeEnum;
use App\Traits\Model\DocumentsTrait;
use App\Enums\ThirdPartyTypeEnum;

class ThirdParties extends Model
{
    use SoftDeletes, DocumentsTrait, ImageTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_ThirdParties';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartyId';
    }

    protected $fillable = [
        'ThirdPartyName',
        'TradingName',
        'BusinessType',
        'RegistrationNumber',
        'TaxPIN',
        'VATNumber',
        'CountryId',
        'PhysicalAddress',
        'Email',
        'Phone',
        'Website',
        'Status',
        'ThirdPartyType',
        'IsPrequalified',
        'ApprovalStatus',
        'IDNumber',
        'PassportNo',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'BusinessType' => BusinessTypeEnum::class,
        'Status' => ThirdPartyStatusEnum::class,
        'ApprovalStatus' => ThirdPartyApprovalStatusEnum::class,
        'IsPrequalified' => 'boolean',
    ];

    // you can only prequalify suppliers
    protected static function booted()
    {
        static::saving(function ($model) {
            if (!is_null($model->IsPrequalified)) {
                // Ensure at least one pivot type has supplier code pattern when marking prequalified
                $model->loadMissing('types');
                $isSupplier = $model->types->pluck('Code')->contains(fn($c) => str_starts_with($c, 'SU-'));
                if (!$isSupplier) {
                    throw new \LogicException("Only supplier third parties (with SU-* type) can be marked as prequalified.");
                }
            }
        });

        static::creating(function ($model) {
            $model->ApprovalStatus = ThirdPartyApprovalStatusEnum::Pending;
            $model->Status = ThirdPartyStatusEnum::Inactive;
        });
    }

    public function getPrimaryTypeAttribute(): ?ThirdPartyTypeEnum
    {
        if ($this->relationLoaded('types') && $this->types->count() === 1) {
            $newTypeCode = $this->types->first()->Code;

            if (str_starts_with($newTypeCode, 'SU-')) {
                return ThirdPartyTypeEnum::Supplier;
            }
            if (str_starts_with($newTypeCode, 'TE-')) {
                return ThirdPartyTypeEnum::Tenant;
            }
            if (str_starts_with($newTypeCode, 'CU-')) {
                return ThirdPartyTypeEnum::Customer;
            }
        }
        return $this->ThirdPartyType; // fallback to legacy enum
    }

    public function getPrimaryTypeLabelAttribute(): string
    {
        return $this->primary_type?->label() ?? 'N/A';
    }

    public function isSupplier(): bool
    {
        if ($this->relationLoaded('types')) {
            return $this->types->pluck('Code')->contains(fn($c) => str_starts_with($c, 'SU-'));
        }
        return $this->ThirdPartyType === ThirdPartyTypeEnum::Supplier;
    }

    public function isTenant(): bool
    {
        if ($this->relationLoaded('types')) {
            return $this->types->pluck('Code')->contains(fn($c) => str_starts_with($c, 'TE-'));
        }
        return $this->ThirdPartyType === ThirdPartyTypeEnum::Tenant;
    }

    public function isCustomer(): bool
    {
        if ($this->relationLoaded('types')) {
            return $this->types->pluck('Code')->contains(fn($c) => str_starts_with($c, 'CU-'));
        }
        return $this->ThirdPartyType === ThirdPartyTypeEnum::Customer; // fall back
    }

    public function isApproved(): bool
    {
        return $this->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved;
    }

    public function getLabelAttribute(): string
    {
        return $this->ThirdPartyName ?: $this->TradingName ?: "Unnamed #{$this->Id}";
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'LocationId', 'ID');
    }

    // can associate one user to a thirdparty
    public function user(): HasOne
    {
        return $this->hasOne(ThirdPartyUser::class, 'ThirdPartyId', 'Id');
    }

    public function bankDetails(): HasMany
    {
        return $this->hasMany(ThirdPartiesBankDetails::class, 'ThirdPartyId', 'Id');
    }

    public function categories(): BelongsToMany
    {
        // Pivot uses snake_case columns in this table: third_party_id, supplier_category_id @Kimxons
        return $this->belongsToMany(
            SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id'
        );
    }

    public function scopeSuppliers(): BelongsToMany
    {
        return $this->types()->wherePivot('PartyType', SupplierMaster::getPrimaryKey());
    }

    // @Kimxons: thirdparty can have multiple types 
    public function types(): BelongsToMany
    {
        return $this->types()->wherePivot('PartyType', BancassuranceCustomer::getPrimaryKey());
    }

    public function scopeTenants(): BelongsToMany
    {
        return $this->types()->wherePivot('PartyType', PropertyNewTenant::getPrimaryKey());
    }

    public function wallet()
    {
        return $this->hasOne(\App\Models\Finance\CustomerWallet::class, 'CustomerID', 'Id');
    }

    public function creditProfiles(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\FinanceCreditManagement::class, 'CustomerID', 'Id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\FinanceInvoice::class, 'CustomerID', 'Id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\FinanceReceipt::class, 'CustomerID', 'Id');
    }


    /**
     * =======================================| TODO: Upto there the rest to be removed or moved to appropriate classes  |======================================
     */


    public function categories(): BelongsToMany
    {
        // Pivot uses snake_case columns in this table: third_party_id, supplier_category_id
        //todo move to supplier master model
        return $this->belongsToMany(
            SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id'
        );
    }


    /**
     * Legacy category mappings (t_ThirdPartiesCategories -> CodeDetail) used by prequalification screen.
     */
    public function legacyCategories()
    {  //todo move to supplier master model
        return $this->hasMany(\App\Models\ThirdParty\ThirdPartyCategory::class, 'ThirdPartyId', 'Id')
            ->whereNull('DeletedOn')
            ->with('category');
    }


}
