<?php

namespace App\Models\HR\Exit;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitClearance extends Model
{
    protected $table = 't_HRExitClearances';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'ClearanceDepartmentID',
        'Status',
        'ClearedBy',
        'ClearedOn',
        'Remarks',
        'Details',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'ClearedOn' => 'datetime',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(ExitClearanceDepartment::class, 'ClearanceDepartmentID');
    }

    public function clearedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ClearedBy', 'Id')->withTrashed();
    }
}
