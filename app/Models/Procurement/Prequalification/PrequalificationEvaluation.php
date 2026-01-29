<?php

namespace App\Models\Procurement\Prequalification;

use App\Models\Auth\User;
use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationEvaluation extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_PrequalificationEvaluations';
    protected $primaryKey = 'EvaluationID';

    protected $fillable = [
        'ApplicationID',
        'EvaluatorID',
        'SectionID',
        'CriteriaID',
        'Score',
        'MaxScore',
    ];

    protected $casts = [
        'Score' => 'float',
        'MaxScore' => 'float',
        'EvaluatedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // This is a single prequalification appplication - comes from the supplier portal
    public function application(): BelongsTo
    {
        return $this->belongsTo(PrequalificationApplication::class, 'ApplicationID', 'ApplicationID');
    }

    // Evaluator is of type User
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'EvaluatorID', 'UserID');
    }

    // Section to be evaluated
    public function evaluationSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'SectionID', 'Id');
    }

    // criteria within the section to be evaluated
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'CriteriaID', 'Id');
    }
}
