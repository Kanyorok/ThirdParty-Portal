<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestionResponse extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_SurveyQuestionResponses';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'SurveyQuestionResponsesId';
    }

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
