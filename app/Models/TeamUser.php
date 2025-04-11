<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamUser extends Model
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_TeamUser';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'TeamId',
                           'UserId',
                           'CreatedBy',
                           'ModifiedBy',
                          ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'TeamId', 'TeamID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }
}
