<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Traits\Model\SpecialPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repository extends Model
{
    use SoftDeletes, UserActorTrait, SpecialPermissionTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Repositories';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'Description', 'RepositoryId', 'Visibility', 'ParentId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Visibility' => VisibilityEnum::class,
        'ParentId' => 'integer',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'ParentId', 'Id');
    }


    public function getShareEmailSubject(): string
    {
        return 'Notification: #permission permission to ' . $this->Name;
    }
}
