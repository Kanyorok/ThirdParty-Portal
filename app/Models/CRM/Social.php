<?php

namespace App\Models\CRM;

use App\Enums\Core\IntegrationsEnum;
use App\Models\Communication\Comment;
use App\Models\DMS\Image;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Social extends Model
{
    use ImageTrait, SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Socials';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'SocialID',
                           'RemoteId',
                           'Type',
                           'Content',
                           'LikesCount',
                           'CommentsCount',
                           'ViewsCount',
                           'Published_at',
                           'Scheduled_at',
                           'Response',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'LikesCount'    => 'integer',
                        'CommentsCount' => 'integer',
                        'ViewsCount'    => 'integer',
                        'Published_at'  => 'datetime',
                        'Scheduled_at'  => 'datetime',
                        'Response'      => 'array',
                        'Type'          => IntegrationsEnum::class,
                       ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
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
        return $this->belongsToMany(Image::class, 't_SocialImage', 'SocialId', 'ImageId', 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy'])->withTimestamps();
    }


    public function getPhotoAttribute()
    {
        return $this->images()->whereLike('t_Images.ImageType', 'image/%')->first();
    }

    protected function getImageName(): string
    {
        return $this->SocialID;
    }
}
