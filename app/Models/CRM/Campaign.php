<?php

namespace App\Models\CRM;

use App\Enums\CampaignStatusEnum;
use App\Enums\CampaignTypeEnum;
use App\Models\CRM\Approval\PendingWorkflow;
use App\Models\CRM\Approval\Workflow;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Campaigns';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CampaignID', 'Label', 'Status', 'Type', 'Details', 'MarketingListId', 'Processing', 'Notes',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Status' => CampaignStatusEnum::class,
        'Type' => CampaignTypeEnum::class,
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'Processing' => 'boolean',
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
        return 'CampaignID';
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(MarketingList::class, 'MarketingListId', 'MarketingListID');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CampaignParty::class, 'CampaignId');
    }


    public function workflows(): MorphMany
    {
        return $this->morphMany(Workflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }

    public function pendingWorkflows(): MorphMany
    {
        return $this->morphMany(PendingWorkflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }
}
