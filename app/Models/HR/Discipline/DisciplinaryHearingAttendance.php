<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryHearingAttendance extends Model
{
    protected $table = 't_HRDisciplinaryHearingAttendance';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'HearingID',
        'AttendeeName',
        'AttendeeRole',
        'Present',
        'Notes',
    ];

    protected $casts = [
        'Present' => 'boolean',
    ];

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryHearing::class, 'HearingID');
    }
}
