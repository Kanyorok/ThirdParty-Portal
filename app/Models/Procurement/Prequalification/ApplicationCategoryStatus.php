<?php

namespace App\Models\Procurement\Prequalification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationCategoryStatus extends Model
{
    public $timestamps = false;
    protected $table = 't_ApplicationCategoryStatus';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ApplicationId', 'CategoryId', 'Status', 'ProgressPercent',
        'Stage', 'StageLabel', 'DecisionDate', 'RejectionReason',
        'ReviewerNotes', 'CreatedBy', 'CreatedOn', 'ModifiedBy',
        'ModifiedOn', 'DeletedBy', 'DeletedOn'
    ];

    protected $casts = [
        'ProgressPercent' => 'decimal:2',
        'DecisionDate' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PrequalificationApplication::class, 'ApplicationId', 'ApplicationID');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ThirdParty\SupplierCategory::class, 'CategoryId', 'SupplierCategoryID');
    }

    public function progressHistory(): HasMany
    {
        return $this->hasMany(CategoryProgressHistory::class, 'ApplicationCategoryId', 'Id');
    }
}
