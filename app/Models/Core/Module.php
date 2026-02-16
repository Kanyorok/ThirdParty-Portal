<?php

namespace App\Models\Core;

use App\Traits\Model\RelatedPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use RelatedPermissionTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Modules';
    protected $primaryKey = 'ModuleID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ModuleID',
        'Name',
        'Description',
        'ParentID',
        'Icon',
        'Route',
        'RequiredPermission',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ModuleID';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'ParentID', 'ModuleID');
    }

    public function children(): HasMany
    {
        return $this->hasMany(__CLASS__, 'ParentID', 'ModuleID');
    }

    public function permissionColum(): string
    {
        return 'RequiredPermission';
    }
}
