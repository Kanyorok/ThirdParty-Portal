<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DiscussionUser extends Pivot
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    protected $table = 't_DiscussionsUsers';

    protected $fillable = [
        'DiscussionId',
        'UserID',
        'CreatedOn',
        'CreatedBy',
        'ModifiedOn',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DiscussionUsersId';
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'DiscussionId', 'DiscussionID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }
}
