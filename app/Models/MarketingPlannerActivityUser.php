<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingPlannerActivityUser extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
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
