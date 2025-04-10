<?php

namespace App\Models;

use App\Enums\Core\RoleEnum;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadUser extends Model
{
    use ImageTrait, UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_LeadUsers';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeadId', 'Party', 'PartyID', 'Role',
        'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Role' => RoleEnum::class,
        'LeadId' => 'integer',
    ];

    /**
     * User or Team
     */
    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Party', 'PartyID')->withTrashed();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'LeadId', 'Id');
    }
}
