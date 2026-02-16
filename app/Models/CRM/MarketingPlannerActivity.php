<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingPlannerActivity extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_MarketingPlannerActivities';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'PlannerActivityID', 'PlannerId', 'Name', 'Location', 'Notes', 'BranchId', 'StartOn', 'EndOn', 'Budget',
        'Materials', 'Actual', 'MasterPlannerId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy', 'DeletedOn',
    ];

    protected $casts = [
        'StartOn' => 'datetime',
        'EndOn' => 'datetime',
        'Budget' => 'decimal:2',
        'Actual' => 'decimal:2',
    ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'PlannerActivityID';
    }

    public function planner(): BelongsTo
    {
        return $this->belongsTo(MarketingPlanner::class, 'PlannerId', 'Id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'Id');
    }

    public function activityUsers(): HasMany
    {
        return $this->hasMany(MarketingPlannerActivityUser::class, 'ActivityId', 'Id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 't_MarketingPlannerActivityUsers', 'ActivityId', 'UserID')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy'])->withTimestamps()->withTrashed();
    }
}
