<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Models\Core\SpecialPermission;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repository extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Repositories';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'Description', 'RepositoryId', 'Visibility', 'ParentId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Visibility' => VisibilityEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'RepositoryId';
    }

    public function getRouteKeyName(): string
    {
        return 'RepositoryId';
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'RepositoryId', 'Id');
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(__CLASS__, 'ParentId', 'Id');
    }

    public function permissions(): MorphMany
    {
        return $this->morphMany(SpecialPermission::class, 'party', "Party", "PartyID", 'Id');
    }


}
