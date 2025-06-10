<?php

namespace App\Models\CRM;

use App\Models\ThirdParies\Board;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingBoard extends Model
{
    use UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';

    protected $table = 't_MeetingBoard';


    public static function getPrimaryKey(): string
    {
        return 'MeetingBoardId';
    }

    protected $fillable = [
        'MeetingId', 'BoardMemberId',
        'CreatedBy', 'ModifiedBy',
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
