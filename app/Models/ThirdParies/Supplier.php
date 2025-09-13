<?php

namespace App\Models\ThirdParies;

use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\Tender;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Supplier extends ThirdParties
{
    protected $table = 't_Suppliers';
    protected $primaryKey = 'Id';

    // Limit fillable to actual supplier table columns to avoid parent fillables bleeding in
    protected $fillable = [
        'ThirdPartyID',
        'RoundID',
        'IsPrequalified',
        'Active_Status',
        'CreatedOn',
        'ModifiedOn',
    'CreatedBy',
    'ModifiedBy',
    ];

    protected $casts = [
        'IsPrequalified' => 'boolean',
    'Active_Status' => 'boolean',
    ];

    protected static function booted()
    {
        // Intentionally empty: suppress parent ThirdParties booted() logic that sets ApprovalStatus/Status
        // because t_Suppliers does not have those columns.
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
        // Simplified: treat Active_Status true & IsPrequalified true as approved
        return $query
            ->whereHas('types', fn($q) => $q->where('Code', 'like', 'SU-%'))
            ->where('IsPrequalified', true)
            ->where('Active_Status', 1);
    }

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
