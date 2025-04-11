<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DiscussionUser extends Pivot
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $table = 't_DiscussionsUsers';

    protected $fillable = [
                           'DiscussionId',
                           'UserID',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'DiscussionId', 'DiscussionID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }
}
