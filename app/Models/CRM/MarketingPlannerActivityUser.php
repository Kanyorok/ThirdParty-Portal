<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingPlannerActivityUser extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_MarketingPlannerActivityUsers';

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
