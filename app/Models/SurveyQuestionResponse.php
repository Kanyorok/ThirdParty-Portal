<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestionResponse extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_SurveyQuestionResponses';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'Party',
                           'PartyID',
                           'Source',
                           'SourceID',
                           'Response',
                           'SurveyQuestionAnswerID',
                           'SurveyQuestionID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestionAnswer::class, 'SurveyQuestionAnswerID', 'Id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestionAnswer::class, 'SurveyQuestionID', 'Id');
    }
}
