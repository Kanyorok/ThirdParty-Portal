<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingPlannerActivityUser extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_MarketingPlannerActivityUsers';

    public static function getPrimaryKey(): string
    {
        return 'MarketingPlannerActivityUsersId';
    }

    protected $fillable = [
                           'ActivityId',
                           'UserID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'Id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(MarketingPlannerActivity::class, 'ActivityId', 'Id');
    }
}
