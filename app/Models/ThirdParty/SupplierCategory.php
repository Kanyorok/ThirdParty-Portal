<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierCategory extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierCategories';
    protected $primaryKey = 'SupplierCategoryID';

    protected $fillable = [
        'CategoryName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'IsActive' => 'boolean',
    ];

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 't_ThirdPartySupplierCategory', 'supplier_category_id', 'third_party_id');
    }

    public function thirdParties(): BelongsToMany
    {
        return $this->belongsToMany(ThirdParties::class, 't_ThirdParty_SupplierCategory', 'supplier_category_id', 'third_party_id');
    }
}
