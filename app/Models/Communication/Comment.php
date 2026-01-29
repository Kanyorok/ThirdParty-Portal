<?php

namespace App\Models\Communication;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Comments';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Notes',
        'CommentType',
        'CommentTypeID',
        'Response',
        'RemoteId',
        'Source',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'Response' => 'object',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'CommentId';
    }

    public function type(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'CommentType', 'CommentTypeID');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(__CLASS__, 'type', 'CommentType', 'CommentTypeID', 'Id');
    }
}
