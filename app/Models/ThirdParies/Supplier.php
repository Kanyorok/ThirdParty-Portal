<?php

namespace App\Models\ThirdParies;

use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\Tender;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * This Model is used as a t_Suppliers_Categories for supplier use t_SupplierMaster
 */
class Supplier extends Model
{
    protected $table = 't_Suppliers';
    protected $primaryKey = 'Id';

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    protected $fillable = [
        'RoundID',
        'SupplierMasterId',
        'RoundID',
        'CategoryId',
        'Active_Status',
        'SupplierCategoryID',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'Active_Status' => 'boolean',
        'CategoryId' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'SupplierCategoriesId'; // You kiding,right?
    }

    /**
     * @deprecated use supplierMaster
     */
    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(SupplierMaster::class, 'SupplierMasterId', 'Id');
    }

    public function supplierMaster(): BelongsTo
    {
        return $this->belongsTo(SupplierMaster::class, 'SupplierMasterId', 'Id');
    }

    public function rfqEvaluations(): HasMany
    {
        return $this->hasMany(RFQEvaluation::class, 'SupplierId', 'Id');
    }

    public function procurementPeriods(): BelongsToMany
    {
        return $this->belongsToMany(ProcurementPeriod::class, 't_ProcurementPeriodSupplier', 'SupplierId', 'ProcurementPeriodId', 'Id', 'Id');
    }

    public function rfqLines(): HasMany
    {
        return $this->hasMany(RFQLine::class, 'SupplierId', 'Id');
    }

    public function rfqs(): BelongsToMany
    {
        return $this->belongsToMany(RFQ::class, 't_RFQ_Supplier', 'SupplierId', 'RFQId', 'Id', 'RFQId')
            ->withPivot('Status')
            ->withTimestamps();
    }

    public function tenders(): BelongsToMany
    {
        return $this->belongsToMany(Tender::class, 'TenderSupplier', 'SupplierID', 'TenderID', 'Id', 'TenderID')
            ->withTimestamps();
    }

    public function prequalificationApplications(): HasMany
    {
        return $this->hasMany(PrequalificationApplication::class, 'SupplierID', 'Id');
    }

    public function scopeApprovedAndPrequalified($query)
    {
        // Treat Active_Status true as approved/active supplier row for the round/category
        return $query
            ->whereHas('types', fn ($q) => $q->where('Code', 'like', 'SU-%'))
            ->where('Active_Status', 1);
    }

    /**
     * Relationship to SupplierCategory via SupplierCategoryID
     */
    public function supplierCategory()
    {
        return $this->belongsTo(\App\Models\ThirdParty\SupplierCategory::class, 'SupplierCategoryID', 'SupplierCategoryID');
    }

    /**
     * Relationship to SupplierCategory via CategoryId (populated by prequalification)
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\ThirdParty\SupplierCategory::class, 'CategoryId', 'SupplierCategoryID');
    }

    /**
     * Relationship to PrequalificationRound via RoundID
     */
    public function round()
    {
        return $this->belongsTo(\App\Models\Procurement\Prequalification\PrequalificationRound::class, 'RoundID', 'RoundID');
    }

    /**
     * Many-to-many relationship to SupplierCategories through pivot table
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\ThirdParty\SupplierCategory::class,
            't_ThirdParty_SupplierCategory',
            'third_party_id',
            'supplier_category_id'
        )->withTimestamps();
    }

    public function scopeOnlySuppliers($query)
    {
        return $query->whereHas('types', fn ($q) => $q->where('Code', 'like', 'SU-%'));
    }
}
