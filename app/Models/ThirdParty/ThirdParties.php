<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\SupplierMaster;;
use App\Models\Finance\CustomerWallet;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceReceipt;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\ImageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'ThirdPartyName', 'TradingName', 'BusinessType', 'RegistrationNumber', 'TaxPIN', 'VATNumber', 'CountryId', 'LocationId', 'PhysicalAddress', 'Email', 'Phone',
        'ImageId', 'Website', 'Status', 'Extra', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'Extra' => 'array',
    ];

    // -------------------- Relationships --------------------

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'BusinessType', 'Id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'Id');
    }

    public function bankDetails(): HasMany
    {
        return $this->hasMany(ThirdPartiesBankDetails::class, 'ThirdPartyId', 'Id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(ThirdPartyUser::class, 'ThirdPartyId', 'Id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'LocationId', 'ID');
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(ThirdPartyType::class, 't_ThirdPartyType_ThirdParties', 'ThirdPartyId', 'TypeId')
            ->withTimestamps('CreatedOn', 'ModifiedOn')
            ->withPivot('Id', 'PartyType', 'PartyID', 'CreatedBy', 'ModifiedBy', 'DeletedBy')
            ->wherePivotNull('DeletedOn');
    }

    public function scopeSuppliers(): BelongsToMany
    {
        return $this->types()->wherePivot('PartyType', SupplierMaster::getPrimaryKey());
    }

    public function scopeCustomers(): BelongsToMany
    {
        return $this->types()->wherePivot('PartyType', BancassuranceCustomer::getPrimaryKey());
    }

    public function scopeTenants(): BelongsToMany
    {
        return $this->types()->wherePivot('PartyType', PropertyNewTenant::getPrimaryKey());
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(CustomerWallet::class, 'CustomerID', 'Id');
    }

    public function creditProfiles(): HasMany
    {
        return $this->hasMany(FinanceCreditManagement::class, 'CustomerID', 'Id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FinanceInvoice::class, 'CustomerID', 'Id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FinanceReceipt::class, 'CustomerID', 'Id');
    }

    // -------------------- Additional / Custom Relationships --------------------

    /**
     * Supplier information
     */
    public function supplierInfo(): HasOne
    {
        return $this->hasOne(SupplierMaster::class, 'ThirdPartyId', 'Id')
            ->with(['products', 'contracts']);
    }

    /**
     * Customer details
     */
    public function customerDetails(): HasOne
    {
        return $this->hasOne(BancassuranceCustomer::class, 'ThirdPartyId', 'Id');
    }

    /**
     * Tenant details
     */
    public function tenantDetails(): HasOne
    {
        return $this->hasOne(PropertyNewTenant::class, 'ThirdPartyId', 'Id');
    }

    /**
     * Categories
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id'
        );
    }

    /**
     * Legacy categories
     */
    public function legacyCategories(): HasMany
    {
        return $this->hasMany(ThirdPartyCategory::class, 'ThirdPartyId', 'Id')
            ->whereNull('DeletedOn')
            ->with('category');
    }

    // -------------------- Helper / Accessor --------------------

    protected function getImageName(): string
    {
        return $this->ThirdPartyName;
    }

    public function getLabelAttribute(): string
    {
        return $this->ThirdPartyName ?: $this->TradingName ?: "Unnamed #{$this->Id}";
    }
}
