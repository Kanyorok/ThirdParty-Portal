<?php

namespace App\Models\Core;

use App\Enums\Core\RoleEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialPermission extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_SpecialPermissions';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Permission', 'Party', 'PartyID', 'Model', 'ModelID',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Permission' => RoleEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'SpecialPermissionId';
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Party', 'PartyID');//->withTrashed();
    }

    public function model(): BelongsTo
    {
        return $this->morphTo(__FUNCTION__, 'Model', 'ModelID');
    }
}
