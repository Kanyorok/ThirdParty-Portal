<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingBoard extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $table = 't_MeetingBoard';

    protected $fillable = [
                           'MeetingId',
                           'BoardMemberId',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    protected $casts = [
                        'CreatedOn'  => 'datetime',
                        'ModifiedOn' => 'datetime',
                       ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'BoardMemberId', 'Id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'MeetingId', 'MeetingID');
    }
}
