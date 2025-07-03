<?php

namespace App\Models\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Exceptions\ErroredException;
use App\Services\DMS\ImageService;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Images';
    protected $primaryKey = 'ImageID';

    public static function getPrimaryKey(): string
    {
        return 'ImageID';
    }

    protected $fillable = [
        "Name", "ImageType", "ImageTypeID", "Image", "MIMEType", 'Notes',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "ImageType", "ImageTypeID")->withTrashed();
    }

    /**
     * image_src
     *
     * @return string
     */
    public function getImageSrcAttribute(): string
    {
        return "data:" . $this->MIMEType . ";base64," . $this->Image;
    }

    public function ext(): ?ExtensionsEnum
    {
        try {
            return ExtensionsEnum::fromMimeType($this->MIMEType);
        } catch (ErroredException) {
            return null;
        }
    }

    public function service(): ImageService
    {
        return new ImageService($this);
    }
}
