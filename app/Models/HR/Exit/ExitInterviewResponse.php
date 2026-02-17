<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitInterviewResponse extends Model
{
    protected $table = 't_HRExitInterviewResponses';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'QuestionID',
        'Answer',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExitInterviewQuestion::class, 'QuestionID');
    }
}
