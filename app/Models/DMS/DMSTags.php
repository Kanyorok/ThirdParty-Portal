<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DMSTags extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DMSTags';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'TagID', 'Visibility', 'Description',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Visibility' => VisibilityEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DMSId';
    }

    public function getRouteKeyName(): string
    {
        return 'TagID';
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 't_DocumentTags', 'TagId', 'DocId', 'Id', 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy'])->withTimestamps(); //->using(DocumentTags::class);
    }

    public function rules(): HasMany|DMSTags
    {
        return $this->hasMany(DocumentTaggingRules::class, 'TagId', 'Id');
    }
}
