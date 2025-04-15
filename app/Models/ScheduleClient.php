<?php

namespace App\Models;

use App\Models\BR\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleClient extends Pivot
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_ScheduleClients';

    protected $fillable = [
                           'ScheduleId',
                           'ClientID',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                           'ReminderOn',
                          ];

    protected $casts = [
                        'CreatedOn'  => 'datetime',
                        'ModifiedOn' => 'datetime',
                        'ReminderOn' => 'datetime',
                       ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID');
    }
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'ScheduleId', 'ScheduleID');
    }
}
