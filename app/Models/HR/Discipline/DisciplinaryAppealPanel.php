<?php

namespace App\Models\HR\Discipline;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryAppealPanel extends Model
{
    protected $table = 't_HRDisciplinaryAppealPanel';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AppealID',
        'EmployeeID',
        'Role',
    ];

    public function appeal(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryAppeal::class, 'AppealID');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
