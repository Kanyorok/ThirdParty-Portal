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
use App\Models\Inventory\ItemCategories;

class ThirdParties extends Model
{
    use SoftDeletes;

    const CREATED_AT        = 'CreatedOn';
    const UPDATED_AT        = 'ModifiedOn';
    const DELETED_AT        = 'DeletedOn';

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
        'IsPrequalified',
        'ApprovalStatus',
        'CategoryId',
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

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!is_null($model->IsPrequalified) && $model->ThirdPartyType !== ThirdPartyTypeEnum::Supplier) {
                throw new \LogicException("Only suppliers can be marked as prequalified.");
            }
        });

        static::creating(function ($model) {
            $model->ApprovalStatus = ThirdPartyApprovalStatusEnum::Pending;
            $model->Status = ThirdPartyStatusEnum::Inactive;
        });
    }

    public function setIsPrequalifiedAttribute($value)
    {
        $this->attributes['IsPrequalified'] = $value;
    }

    public function users(): HasOne
    {
        return $this->hasOne(ThirdPartyUser::class, 'ThirdPartyId');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ItemCategories::class, 't_ItemCategories', 'ThirdPartyId', 'CategoryID', 'Id', 'Id')
            ->withPivot('CreatedBy', 'ModifiedBy', 'DeletedBy', 'CreatedOn', 'ModifiedOn', 'DeletedOn');
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
        return $this->TaxPIN ?: $this->RegistrationNumber;
    }

    public function bankDetails(): HasMany
    {
        return $this->hasMany(ThirdPartiesBankDetails::class, 'ThirdPartyId', 'Id');
    }
}
