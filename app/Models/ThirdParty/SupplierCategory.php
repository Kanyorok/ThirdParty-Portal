<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ThirdParies\Supplier;
use App\Models\Procurement\Prequalification\PrequalificationApplication;

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
        return $this->belongsToMany(
            Supplier::class,
            't_ThirdParty_SupplierCategory',
            'SupplierCategoryID',
            'ThirdPartyID'
        )->withTimestamps();
    }

    public function prequalificationApplications(): BelongsToMany
    {
        return $this->belongsToMany(
            PrequalificationApplication::class,
            't_PrequalificationApplicationCategories',
            'CategoryID',
            'ApplicationID'
        )->withTimestamps();
    }
}
