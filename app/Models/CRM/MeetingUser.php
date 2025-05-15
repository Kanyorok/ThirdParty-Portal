<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingUser extends Model
{
    use  UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';

    protected $table = 't_MeetingUsers';

    protected $fillable = [
                           'MeetingId',
                           'UserID',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    protected $casts = [
                        'CreatedOn'  => 'datetime',
                        'ModifiedOn' => 'datetime',
                       ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'MeetingId', 'MeetingID');
    }
}
