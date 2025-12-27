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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function users(): HasOne
    {
        return $this->hasOne(ThirdPartyUser::class, 'ThirdPartyId', 'Id');
    }

    public function isSupplier(): bool
    {
        return $this->types()->wherePivot('PartyType', 'SupplierMasterId')->exists();
    }

    public function isTenant(): bool
    {
        return $this->types()->where('t_ThirdPartyTypes.TypeId', 4)->exists();
    }

    public function isCustomer(): bool
    {
        return $this->types()->where('t_ThirdPartyTypes.TypeId', 6)->exists();
    }

    protected function getImageName(): string
    {
        return $this->ThirdPartyName ?? 'third-party';
    }
}
