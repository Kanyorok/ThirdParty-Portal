<?php

namespace App\Models\ThirdParies;

use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQEvaluation;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\Tender;
use App\Models\Procurement\PrequalificationApplication;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum; // Import the enum
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Supplier extends ThirdParties
{
    protected $table = 't_ThirdParties';
    protected $primaryKey = 'Id';

    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('prequalifiedAndApprovedSupplier', function ($query) {
            $query->where('ThirdPartyType', ThirdPartyTypeEnum::Supplier)
                ->where('IsPrequalified', true)
                ->where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Approved);
        });
    }

    protected $fillable = [
        'IsPrequalified',
    ];

    protected $casts = [
        'IsPrequalified' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategories::class, 'CategoryId', 'Id');
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
}
