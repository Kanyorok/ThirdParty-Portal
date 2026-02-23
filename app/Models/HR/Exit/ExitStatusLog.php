<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitStatusLog extends Model
{
    protected $table = 't_HRExitStatusLogs';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'FromStatus',
        'ToStatus',
        'Remarks',
        'ChangedBy',
        'ChangedOn',
    ];

    protected $casts = [
        'ChangedOn' => 'datetime',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }
}
