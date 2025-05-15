<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestionAnswer extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_SurveyQuestionAnswers';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'Answer',
                           'Notes',
                           'SurveyQuestionID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'SurveyQuestionID', 'Id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyQuestionResponse::class, 'SurveyQuestionAnswerID', 'Id');
    }
}
