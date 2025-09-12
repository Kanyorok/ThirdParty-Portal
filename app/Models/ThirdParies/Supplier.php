<?php

namespace App\Models\ThirdParies;

use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\Tender;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Supplier extends ThirdParties
{
    protected $table = 't_Suppliers';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'IsPrequalified',
    ];

    protected $casts = [
        'IsPrequalified' => 'boolean',
    ];

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
        return $query
            ->whereHas('types', fn($q) => $q->where('Code', 'like', 'SU-%'))
            ->where('IsPrequalified', true)
            ->where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Approved);
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
