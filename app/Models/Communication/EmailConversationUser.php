<?php

namespace App\Models\Communication;

use App\Enums\Core\RoleEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConversationUser extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public static function getPrimaryKey(): string
    {
        return (new self())->primaryKey;
    }

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_EmailConversationUsers';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'EmailConversationId',
                           'Party',
                           'PartyID',
                           'Role',
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'Role' => RoleEnum::class,
                        'EmailConversationId' => 'integer',
                       ];

    /**
     * User or Team
     */
    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Party', 'PartyID')->withTrashed();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(EmailConversation::class, 'EmailConversationId', 'Id');
    }
}
