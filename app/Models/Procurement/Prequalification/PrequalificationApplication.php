<?php

namespace App\Models\Procurement\Prequalification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ThirdParty\SupplierCategory;
use App\Models\ThirdParty\ThirdParties;
use App\Enums\Procurement\PrequalificationApplicationEnum;

class PrequalificationApplication extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierPrequalificationApplications';
    protected $primaryKey = 'ApplicationID';

    protected $fillable = [
        'SupplierID',
        'RoundID',
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

    public function supplier()
    {
        return $this->belongsTo(ThirdParties::class, 'SupplierID', 'Id');
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(PrequalificationRound::class, 'RoundID', 'RoundID');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(PrequalificationEvaluation::class, 'ApplicationID', 'ApplicationID');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            SupplierCategory::class,
            't_PrequalificationApplicationCategories',
            'ApplicationID',
            'CategoryID'
        )->withTimestamps();
    }
}
