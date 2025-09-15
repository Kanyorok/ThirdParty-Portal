<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceAlert extends Model
{
    protected $table = 't_ComplianceAlerts';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CalendarEntryID','DaysBefore','EscalationLevel','Channel',
        'IsActive','CreatedBy','CreatedOn'
    ];

    public function calendarEntry()
    {
        return $this->belongsTo(ComplianceCalendarEntry::class, 'CalendarEntryID');
    }
}
