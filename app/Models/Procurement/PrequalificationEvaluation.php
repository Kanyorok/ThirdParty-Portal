<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Auth\User; // Import User model
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationEvaluation extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_PrequalificationEvaluations';
    protected $primaryKey = 'EvaluationID';

    protected $fillable = [
        'ApplicationID',
        'EvaluatorID',
        'SectionID',
        'CriteriaID',
        'Score',
        'MaxScore',
        'Remarks',
    ];

    protected $casts = [
        'Score' => 'float',
        'MaxScore' => 'float',
        'EvaluatedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PrequalificationApplication::class, 'ApplicationID', 'ApplicationID');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'EvaluatorID', 'UserID');
    }

    public function evaluationSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'SectionID', 'SectionID');
    }
}
