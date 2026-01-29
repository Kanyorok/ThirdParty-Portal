<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceCalendarEntry extends Model
{
    protected $table = 't_ComplianceCalendarEntries';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ObligationID',
        'Title',
        'Description',
        'StartDate',
        'EndDate',
        'OwnerID',
        'IsRecurring',
        'RecurrenceType',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function obligation()
    {
        return $this->belongsTo(ComplianceObligation::class, 'ObligationID');
    }

    public function alerts()
    {
        return $this->hasMany(ComplianceAlert::class, 'CalendarEntryID');
    }
}
