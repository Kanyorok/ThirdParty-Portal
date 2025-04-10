<?php

namespace App\Models;

use App\Enums\Core\RoleEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketUsers extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_TicketUsers';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Party', 'PartyID', 'TicketID', 'Role',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Role' => RoleEnum::class,
        'TicketID' => 'integer'
    ];

    public function tickets(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'TicketID', 'Id');
    }

    /**
     * User or Team
     */
    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Party', 'PartyID')->withTrashed();
    }
}
