<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdParties extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait, ImageTrait;

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

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'BusinessType', 'Id');
        //->where('CodeID', 'BusinessType');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'Id');
        //->where('CodeID', 'ThirdPartyStatus');
    }

    public function bankDetails(): HasMany
    {
        return $this->hasMany(ThirdPartiesBankDetails::class, 'ThirdPartyId', 'Id');
    }

    protected function getImageName(): string
    {
        return $this->ThirdPartyName;
    }

    public function users(): HasMany
    {
        return $this->hasMany(ThirdPartyUser::class, 'ThirdPartyId', 'Id');
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

    /**
     * All Types that the thirdparty has.
     *
     * @return BelongsToMany
     */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(ThirdPartyType::class, 't_ThirdPartyType_ThirdParties', 'ThirdPartyId', 'TypeId')
            // ->using(ThirdPartyTypeTypes::class)
            ->withTimestamps('CreatedOn', 'ModifiedOn')
            ->withPivot('Id', 'PartyType', 'PartyID', 'CreatedBy', 'ModifiedBy', 'DeletedBy')->wherePivotNull('DeletedOn');
    }

    public function scopeSuppliers($query)
    {
        return $query->whereHas('types', function ($q) {
            $q->where('t_ThirdPartyType_ThirdParties.PartyType', SupplierMaster::getPrimaryKey());
        });
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
    public function isApproved(): bool
    {
        return $this->status?->Value === \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Approved->value;
    }
}
