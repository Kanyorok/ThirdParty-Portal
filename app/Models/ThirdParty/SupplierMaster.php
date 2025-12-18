<?php

namespace App\Models\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BR\Product;

class SupplierMaster extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierMaster';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ThirdPartyId',
        'SupplierID',
        'ApprovalStatus',
        'IsPrequalified',
        'Extra',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'Extra' => 'array',
        'ApprovalStatus' => ThirdPartyApprovalStatusEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'SupplierID';
    }

    public function setIsPrequalifiedAttribute($value)
    {
        $this->attributes['IsPrequalified'] = $value;
    }

    /**
     * Prequalification applications submitted by this supplier.
     */
    public function prequalificationApplications()
    {
        return $this->hasMany(\App\Models\Procurement\Prequalification\PrequalificationApplication::class, 'SupplierID', 'Id')
            ->whereNull('DeletedOn')
            ->with('category');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    /**
     * Get supplier categories through pivot table
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id',
            'ThirdPartyId',
            'SupplierCategoryID'
        );
    }

    /**
     * Get item categories through supplier categories
     */
    public function itemCategories()
    {
        // Get item categories through supplier categories
        return $this->hasManyThrough(
            \App\Models\Inventory\ItemCategories::class,
            SupplierCategory::class,
            'SupplierCategoryID',
            'Id',
            'ThirdPartyId',
            'SupplierCategoryID'
        );
    }

    /**
     * Placeholder products relationship - remove or keep as placeholder
     */
    public function products()
    {
        // Return empty relationship for now to avoid errors
        return $this->hasMany(Product::class, 'SupplierId', 'Id')
            ->where('Id', '<', 0);
    }

    /**
     * Placeholder contracts relationship
     */
    public function contracts()
    {
        // Return empty relationship for now
        return $this->hasMany(\App\Models\Contract::class, 'SupplierId', 'Id')
            ->where('Id', '<', 0);
    }
}
