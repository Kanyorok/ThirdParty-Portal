<?php

namespace App\Models\CRM;

use App\Models\BR\Client;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleClient extends Pivot
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_ScheduleClients';

    public static function getPrimaryKey(): string
    {
        return 'ScheduleClientsId';
    }

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
                        'CreatedOn' => 'datetime',
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
