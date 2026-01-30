<?php

namespace App\Models\CRM;

use App\Enums\Core\RoleEnum;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadUser extends Model
{
    use ImageTrait;
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LeadUsers';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeadId', 'Party', 'PartyID', 'Role', 'Notes',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Role' => RoleEnum::class,
        'LeadId' => 'integer',
    ];

    /**
     * User or Team
     */
    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Party', 'PartyID')->withTrashed();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'LeadId', 'Id');
    }

    protected function getImageName(): string
    {
        return "Lead User";
    }

    public static function getPrimaryKey(): string
    {
        return 'LeadUsersId';
    }
}
