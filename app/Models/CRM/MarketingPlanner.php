<?php

namespace App\Models\CRM;

use App\Enums\Marketing\PlannerStatus;
use App\Enums\Marketing\PlannerTypeEnum;
use App\Models\Auth\User;
use App\Models\BR\Branch;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Services\StaticListsService;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingPlanner extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_MarketingPlanner';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'PlannerID',
        'Name',
        'Type',
        'BranchId',
        'OwnerId',
        'MasterPlannerId',
        'Notes',
        'Status',
        'Modes',
        'StartOn',
        'EndOn',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'StartOn' => 'datetime',
        'EndOn' => 'datetime',
        'OwnerId' => 'integer',
        'Type' => PlannerTypeEnum::class,
        'Status' => PlannerStatus::class,
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
        return 'PlannerID';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'OwnerId', 'Id')->withTrashed();
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'MasterPlannerId', 'Id')->withTrashed();
    }

    public function plans(): HasMany
    {
        return $this->hasMany(__CLASS__, 'MasterPlannerId', 'Id')->withTrashed();
    }


    public function mode(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Modes', 'ID')->where('CodeID', StaticListsService::MarketingModes);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'OurBranchID');
    }

    public function workflows(): MorphMany
    {
        return $this->morphMany(Workflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }

    public function pendingWorkflows(): MorphMany
    {
        return $this->morphMany(PendingWorkflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }

    public function activities(): HasMany
    {
        if ($this->Type?->value === PlannerTypeEnum::MasterPlanner->value) {
            return $this->hasMany(MarketingPlannerActivity::class, 'MasterPlannerId', 'Id');
        }
        return $this->hasMany(MarketingPlannerActivity::class, 'PlannerId', 'Id');
    }


}
