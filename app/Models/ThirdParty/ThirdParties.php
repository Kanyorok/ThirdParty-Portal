<?php

namespace App\Models\ThirdParty;

use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdPartyStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\BusinessTypeEnum;

class ThirdParties extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ThirdParties';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ThirdPartyName',
        'TradingName',
        'BusinessType',
        'RegistrationNumber',
        'TaxPIN',
        'VATNumber',
        'Country',
        'PhysicalAddress',
        'Email',
        'Phone',
        'Website',
        'Status',
        'ThirdPartyType',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'ThirdPartyType' => ThirdPartyTypeEnum::class,
        'BusinessType' => BusinessTypeEnum::class,
        'Status' => ThirdPartyStatusEnum::class,
        'ApprovalStatus' => ThirdPartyApprovalStatusEnum::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->ApprovalStatus = ThirdPartyApprovalStatusEnum::Pending;
        });
    }

    public function users(): HasOne
    {
        return $this->hasOne(ThirdPartyUser::class, 'ThirdPartyId');
    }
    // a third party can select multiple categories
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ThirdPartyCategory::class, 't_ThirdPartiesCategories', 'ThirdPartyId', 'CategoryID')
            ->withPivot('CreatedBy', 'ModifiedBy', 'DeletedBy', 'CreatedOn', 'ModifiedOn', 'DeletedOn')
            ->using(ThirdPartyCategory::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->ThirdPartyName ?: $this->TradingName ?: "Unnamed #{$this->Id}";
    }

    public function scopeSuppliers($query)
    {
        return $query->where('ThirdPartyType', ThirdPartyTypeEnum::Supplier);
    }
    public function getKRANoAttribute(): string
    {
        return $this->KraPin ?: $this->RegistrationNumber;
    }
    // TODO: deliberate on the number of bank details a third party can have
    public function bankDetails(): HasMany
    {
        return $this->hasMany(ThirdPartiesBankDetails::class, 'ThirdPartyId', 'Id');
    }
}
