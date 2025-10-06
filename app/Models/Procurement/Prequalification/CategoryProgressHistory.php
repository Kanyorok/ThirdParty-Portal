<?php

namespace App\Models\Procurement\Prequalification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryProgressHistory extends Model
{
    public $timestamps = false;
    protected $table = 't_CategoryProgressHistory';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ApplicationCategoryId', 'PreviousStatus', 'NewStatus',
        'PreviousProgress', 'NewProgress', 'ChangedBy', 'Notes',
        'CreatedBy', 'CreatedOn',
    ];

    protected $casts = [
        'PreviousProgress' => 'decimal:2',
        'NewProgress' => 'decimal:2',
        'CreatedOn' => 'datetime',
    ];

    public function categoryStatus(): BelongsTo
    {
        return $this->belongsTo(ApplicationCategoryStatus::class, 'ApplicationCategoryId', 'Id');
    }
}
