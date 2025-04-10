<?php

namespace App\Models;

use App\Enums\Core\ExtensionsEnum;
use App\Exceptions\ErroredException;
use App\Services\ImageService;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CRMImage extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_CRMImages';
    protected $primaryKey = 'ImageID';

    protected $fillable = [
        "Name", "ImageType", "ImageTypeID", "Image", "MIMEType",
        'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
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
