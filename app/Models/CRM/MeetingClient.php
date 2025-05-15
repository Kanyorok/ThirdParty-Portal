<?php

namespace App\Models\CRM;

use App\Models\BR\Client;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MeetingClient extends Pivot
{
    use UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    protected $table = 't_MeetingClients';

    protected $fillable = [
                           'MeetingId',
                           'ClientID',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    protected $casts = [
                        'CreatedOn'  => 'datetime',
                        'ModifiedOn' => 'datetime',
                       ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'MeetingId', 'MeetingID');
    }
}
