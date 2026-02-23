<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExitInterviewQuestion extends Model
{
    protected $table = 't_HRExitInterviewQuestions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Question',
        'Sequence',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(ExitInterviewResponse::class, 'QuestionID');
    }
}
