<?php

namespace App\Models;

use App\Enums\Core\IntegrationsEnum;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Social extends Model
{
    use SoftDeletes, UserActorTrait, ImageTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Socials';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'SocialID', 'RemoteId', 'Type', 'Content', 'LikesCount', 'CommentsCount', 'ViewsCount', 'Published_at', 'Scheduled_at',
        'Response', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'LikesCount' => 'integer',
        'CommentsCount' => 'integer',
        'ViewsCount' => 'integer',
        'Published_at' => 'datetime',
        'Scheduled_at' => 'datetime',
        'Response' => 'array',
        'Type' => IntegrationsEnum::class
    ];

    public static function getPrimaryKey(): string
    {
        return (new self)->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'SocialID';
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'type', 'CommentType', 'CommentTypeID', 'Id');
    }


    public function images(): BelongsToMany
    {
        return $this->belongsToMany(CRMImage::class, 't_SocialImage', 'SocialId', 'ImageId', 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps();
    }


    public function getPhotoAttribute()
    {
        return $this->images()->whereLike('t_CrmImages.ImageType', 'image/%')->first();

    }
}
