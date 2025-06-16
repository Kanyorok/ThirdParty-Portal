<?php

namespace App\Models\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\VisibilityEnum;
use App\Exceptions\ErroredException;
use App\Models\Core\CategoryMaster;
use App\Models\Core\SpecialPermission;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Documents';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Name", "MimeType", "CategoryId", "RepositoryId", "Visibility",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Visibility' => VisibilityEnum::class,
    ];

    public function ext(): ?ExtensionsEnum
    {
        try {
            return ExtensionsEnum::fromMimeType($this->MIMEType);
        } catch (ErroredException) {
            return null;
        }
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class, 'CategoryId', 'Id')->withAttributes(['Type' => self::getPrimaryKey()]);
    }

    public static function getPrimaryKey(): string
    {
        return 'DocumentId';
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'RepositoryId', 'Id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DMSTags::class, 't_DocumentTags', 'DocumentId', 'TagId', 'Id', 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy'])->withTimestamps();
        //->using(DocumentTags::class);
    }


    public function permissions(): MorphMany
    {
        return $this->morphMany(SpecialPermission::class, 'party', "Party", "PartyID", 'Id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(DocumentVersion::class, 'DocumentId', 'Id')->latest('t_DocumentVersions.Id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'DocumentId', 'Id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(DocumentAttribute::class, 'DocumentId', 'Id');
    }
}
