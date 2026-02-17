<?php

namespace App\Models\HR\Discipline;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryHearingPanel extends Model
{
    protected $table = 't_HRDisciplinaryHearingPanel';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'HearingID',
        'EmployeeID',
        'Role',
    ];

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryHearing::class, 'HearingID');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
