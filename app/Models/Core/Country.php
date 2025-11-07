<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Countries';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name',
        'CountryCode',
        'Iso3',
        'PhoneCode',
        'Flag',
        'CurrencyId',
        'IsActive',
        'SortOrder',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $hidden = [
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'SortOrder' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'CountryID';
    }

    public function localities(): HasMany
    {
        return $this->hasMany(Locality::class, 'CountryId', 'Id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }

    public function thirdParties(): HasMany
    {
        return $this->hasMany(\App\Models\ThirdParty\ThirdParties::class, 'CountryId', 'Id');
    }

    /**
     * Scope to get only active countries
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', 1);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('SortOrder')->orderBy('Name');
    }
}
