<?php

namespace App\Models;

use App\Enums\Feedback\SurveyStatusEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_Surveys';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'SurveyID', 'Label', 'Notes', 'StartOn', 'EndOn', 'Status',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
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
        return (new self)->getRouteKeyName();
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
