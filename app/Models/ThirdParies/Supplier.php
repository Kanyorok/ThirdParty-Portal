<?php

namespace App\Models\ThirdParies;

use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\Tender;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Supplier extends ThirdParties
{
    protected $table = 't_Suppliers';
    protected $primaryKey = 'Id';

    // Limit fillable to actual supplier table columns to avoid parent fillables bleeding in
    protected $fillable = [
        'RoundID',
        'ThirdPartyID',
        'RoundID',
        'CategoryId',
        'Active_Status',
        'SupplierCategoryID',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'Active_Status' => 'boolean',
        'CategoryId' => 'integer',
    ];

    protected static function booted()
    {
        // Intentionally empty: suppress parent ThirdParties booted() logic that sets ApprovalStatus/Status
        // because t_Suppliers does not have those columns.
    }

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyID', 'Id');
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
            ->whereHas('types', fn($q) => $q->where('Code', 'like', 'SU-%'))
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
        return $query->whereHas('types', fn($q) => $q->where('Code', 'like', 'SU-%'));
    }
}
