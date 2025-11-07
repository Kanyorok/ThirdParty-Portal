<?php

namespace App\Models\CRM;

use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestion extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_SurveyQuestions';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'SurveyQuestionsId';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'SurveyQuestionId',
                           'Type',
                           'Question',
                           'Notes',
                           'SurveyId',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'Type' => SurveyQuestionTypeEnum::class,
                       ];

    public function getRouteKeyName(): string
    {
        return 'SurveyQuestionId';
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'SurveyId', 'Id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyQuestionAnswer::class, 'SurveyQuestionID', 'Id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyQuestionResponse::class, 'SurveyQuestionID', 'Id');
    }
}
