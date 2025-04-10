<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestionAnswer extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_SurveyQuestionAnswers';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Answer', 'Notes', 'SurveyQuestionID',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
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
