<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\Insurance\BancassuranceCustomer;

class ThirdParties extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait, ImageTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_ThirdParties';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    public function getMorphClass(): string
    {
        return 'ThirdParties';
    }

    protected $fillable = [
        'ThirdPartyName',
        'TradingName',
        'BusinessType',
        'RegistrationNumber',
        'TaxPIN',
        'VATNumber',
        'CountryId',
        'LocationId',
        'PhysicalAddress',
        'Email',
        'Phone',
        'ImageId',
        'Website',
        'Status',
        'Extra',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'Extra' => 'array',
    ];

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(
            ThirdPartyType::class,
            't_ThirdPartyType_ThirdParties',
            'ThirdPartyId',
            'TypeId',
            'Id',
            'TypeId'
        )
            ->withPivot('PartyType', 'PartyID', 'CreatedBy')
            ->withTimestamps('CreatedOn', 'ModifiedOn');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id',
            'Id',
            'SupplierCategoryID'
        );
    }

    public function supplierMaster(): HasOne
    {
        return $this->hasOne(SupplierMaster::class, 'ThirdPartyId', 'Id');
    }

    public function tenantProfile(): HasOne
    {
        return $this->hasOne(\App\Models\PropertyManagement\PropertyNewTenant::class, 'ThirdPartyId', 'Id');
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(\App\Models\Insurance\BancassuranceCustomer::class, 'ThirdPartyId', 'Id');
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'BusinessType', 'Id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'LocationId', 'ID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'Id');
    }

    public function scopeSuppliers($query)
    {
        return $query->whereHas('types', function ($q) {
            $q->where('t_ThirdPartyType_ThirdParties.PartyType', SupplierMaster::getPrimaryKey());
        });
    }

    // public function isSupplier(): bool
    // {
    //     return $this->types()->wherePivot('PartyType', 'SupplierId')->exists();
    // }

    // public function isTenant(): bool
    // {
    //     return $this->types()->where('t_ThirdPartyTypes.TypeId', 4)->exists();
    // }

    // public function isCustomer(): bool
    // {
    //     return $this->types()->where('t_ThirdPartyTypes.TypeId', 6)->exists();
    // }

    protected function getImageName(): string
    {
        return $this->ThirdPartyName ?? 'third-party';
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


    // public function categories(): BelongsToMany
    // {
    //     // Pivot uses snake_case columns in this table: third_party_id, supplier_category_id
    //     //todo move to supplier master model
    //     return $this->belongsToMany(
    //         SupplierCategory::class,
    //         't_ThirdParty_SupplierCategory',
    //         'third_party_id',
    //         'supplier_category_id'
    //     );
    // }


    /**
     * Legacy category mappings (t_ThirdPartiesCategories -> CodeDetail) used by prequalification screen.
     */
    public function legacyCategories()
    {  //todo move to supplier master model
        return $this->hasMany(\App\Models\ThirdParty\ThirdPartyCategory::class, 'ThirdPartyId', 'Id')
            ->whereNull('DeletedOn')
            ->with('category');
    }
    public function isApproved(): bool
    {
        return $this->status?->Value === \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Approved->value;
    }

    public function isSupplier(): bool
{
    if ($this->relationLoaded('types')) {
        return $this->types->contains(function ($type) {
            return in_array($type->pivot->PartyType, [
                'SupplierMasterId', 
                'SupplierMaster', 
                (new SupplierMaster())->getMorphClass()
            ]) || (isset($type->Code) && str_starts_with($type->Code, 'SU'));
        });
    }
    return $this->supplierMaster()->exists();
}

public function isTenant(): bool
{
    if ($this->relationLoaded('types')) {
        return $this->types->contains(function ($type) {
            return in_array($type->pivot->PartyType, [
                'PropertyNewTenant',
                'App\Models\PropertyManagement\PropertyNewTenant',
                (new PropertyNewTenant())->getMorphClass()
            ]) || (isset($type->Code) && str_starts_with($type->Code, 'TN')) 
               || $type->TypeId == 1;
        });
    }
    return $this->tenantProfile()->exists();
}

public function isCustomer(): bool
{
    if ($this->relationLoaded('types')) {
        return $this->types->contains(function ($type) {
            return in_array($type->pivot->PartyType, [
                'BancassuranceCustomer',
                'App\Models\Insurance\BancassuranceCustomer',
                (new BancassuranceCustomer())->getMorphClass()
            ]) || (isset($type->Code) && str_starts_with($type->Code, 'CU'));
        });
    }
    return $this->customerProfile()->exists();
}
}
