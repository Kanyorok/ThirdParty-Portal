<?php

namespace App\Models\Auth;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamUser extends Model
{
    use  UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $table = 't_TeamUser';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'TeamUserId';
    }

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
