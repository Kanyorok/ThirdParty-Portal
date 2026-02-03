<?php

namespace App\Models\Procurement\Prequalification;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Models\ThirdParty\SupplierCategory;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationApplication extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierPrequalificationApplications';
    protected $primaryKey = 'ApplicationID';

    protected $fillable = [
        'SupplierID',
        'RoundID',
        'CategoryID',
        'SubmittedOn',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'SubmittedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'Status' => PrequalificationApplicationEnum::class,
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierMaster::class, 'SupplierID', 'Id');
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(PrequalificationRound::class, 'RoundID', 'RoundID');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(PrequalificationEvaluation::class, 'ApplicationID', 'ApplicationID');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupplierCategory::class, 'CategoryID', 'SupplierCategoryID');
    }

    /**
     * One-to-one prequalification result.
     */
    public function result(): \Illuminate\Database\Eloquent\Relations\HasOne|PrequalificationApplication
    {
        return $this->hasOne(PrequalificationResult::class, 'ApplicationID', 'ApplicationID');
    }

    /**
     * Virtual attribute to mirror legacy usage of applicationNo in blades.
     * Returns the primary key (ApplicationID) unless a dedicated column is added later.
     */
    public function getApplicationNoAttribute(): string
    {
        return (string)($this->attributes['ApplicationID'] ?? '');
    }

    public function categoryStatuses(): HasMany|PrequalificationApplication
    {
        return $this->hasMany(ApplicationCategoryStatus::class, 'ApplicationId', 'ApplicationID');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PrequalificationApplicationDocument::class, 'ApplicationID', 'ApplicationID');
    }
}
