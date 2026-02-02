<?php

namespace App\Models\CRM;

use App\Enums\Feedback\SurveyStatusEnum;
use App\Models\CRM\Approval\PendingWorkflow;
use App\Models\CRM\Approval\Workflow;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    public const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Surveys';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'SurveyID', 'Label', 'Notes', 'StartOn', 'EndOn', 'Status',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'StartOn' => 'datetime',
        'EndOn' => 'datetime',
        'CreatedBy' => 'integer',
        'Status' => SurveyStatusEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'SurveyID';
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function workflows(): MorphMany
    {
        return $this->morphMany(Workflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }

    public function pendingWorkflows(): MorphMany
    {
        return $this->morphMany(PendingWorkflow::class, __FUNCTION__, 'Source', 'SourceID', 'Id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class, 'SurveyId', 'Id');
    }

    public function responses(): HasManyThrough
    {
        return $this->hasManyThrough(SurveyQuestionResponse::class, SurveyQuestion::class, 'SurveyId', 'SurveyQuestionID', 'Id', 'Id');
    }
}
