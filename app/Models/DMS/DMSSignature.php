<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\ImageGravityEnum;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\SpecialPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DMSSignature extends Model
{
    use SoftDeletes, UserActorTrait, SpecialPermissionTrait, DocumentsTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DMSSignatures';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "SignatureId", "Name", "Description", "Visibility", "ImageId", "SignatureHorizontalStart", "SignatureVerticalStart", "SignatureOpacity",
        "SignatureWidth", "SignatureHeight", "Content", "ContentColour", "ContentSize", "ContentPosition", "ContentBorderColour", "ContentBorderWeight",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Visibility' => VisibilityEnum::class,
        'ContentPosition' => ImageGravityEnum::class,
        "SignatureWidth" => 'integer',
        "SignatureHeight" => 'integer',
        "SignatureHorizontalStart" => 'integer',
        "SignatureVerticalStart" => 'integer',
        "SignatureOpacity" => 'integer',
        "ContentSize" => 'integer',
        "ContentBorderWeight" => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DMSSignatureId';
    }

    public function getRouteKeyName(): string
    {
        return 'SignatureId';
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'ImageId', 'Id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 't_DocumentSignatures', 'SignatureId', 'DocumentId', $this->primaryKey, 'Id');
    }

    public function getShareEmailSubject(): string
    {
        return 'Notification: #permission permission to Signature ' . $this->getSharedName();
    }

    public function getSharedName(): string
    {
        return "#" . $this->SignatureId;
    }

}
