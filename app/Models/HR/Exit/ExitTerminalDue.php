<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitTerminalDue extends Model
{
    protected $table = 't_HRExitTerminalDues';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'ComponentCode',
        'ComponentName',
        'IsEarning',
        'IsTaxable',
        'Amount',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsEarning' => 'boolean',
        'IsTaxable' => 'boolean',
        'Amount' => 'decimal:2',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }
}
