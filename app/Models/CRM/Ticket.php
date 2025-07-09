<?php

namespace App\Models\CRM;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketStatusEnum;
use App\Models\Communication\Comment;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\DMS\Image;
use App\Services\StaticListsService;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Tickets';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'TicketID',
        'Title',
        'CategoryID',
        'Notes',
        'Party',
        'PartyID',
        'Source',
        'SourceID',
        'Status',
        'Priority',
        'Owner',
        'OwnerID',
        'ClosedOn',
        'SourceTicketID',
        'StartDate',
        'EndDate',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'Status' => TicketStatusEnum::class,
        'Priority' => TicketPriorityEnum::class,
        'ClosedOn' => 'datetime',
        'StartDate' => 'datetime',
        'EndDate' => 'datetime',
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
        return 'TicketID';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'CategoryID', 'ID')
            ->where('t_CodeDetails.CodeID', StaticListsService::TicketCategories);
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID")->withTrashed();
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }

    public function assignee(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'Owner', 'OwnerID')->withTrashed();
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(TicketUsers::class, 'TicketID', 'Id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'type', 'CommentType', 'CommentTypeID', 'Id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Image::class, __FUNCTION__, "ImageType", "ImageTypeID", 'Id');
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
