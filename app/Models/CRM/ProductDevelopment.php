<?php

namespace App\Models\CRM;

use App\Models\Auth\User;
use App\Models\Communication\Comment;
use App\Models\Core\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\DMS\Image;
use App\Services\StaticListsService;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDevelopment extends Model
{
    use ImageTrait, SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ProductDevelopment';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ProductID',
        'Name',
        'TargetGroup',
                           'User_ID',
                           'Income',
                           'Revenue',
                           'Regulatory',
                           'Notes',
                           'Justification',
                           'Risks',
                           'RiskStrategies',
                           'Summary',
                           'StageId',
                           'CommentStart',
                           'CommentEnd',
                           'ArchivedOn',
                           'ArchivedBy',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'Income'       => 'decimal:2',
                        'Revenue'      => 'decimal:2',
                        'ArchivedOn'   => 'datetime',
                        'CommentStart' => 'datetime',
                        'CommentEnd'   => 'datetime',
                        'StageId'      => 'integer',
                        'User_ID'      => 'integer',

                       ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'ProductID';
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'StageId')->where('CodeID', StaticListsService::ProductDevelopmentStages);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductDevelopmentFeature::class, 'ProductDevelopmentId', 'Id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'Id');
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

    protected function getImageName(): string
    {
        return $this->Name;
    }
}
