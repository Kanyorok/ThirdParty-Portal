<?php


namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalIPTracking extends Model
{
    protected $table = 't_LegalIPTracking';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'IPID',
        'TrackingType', // e.g., Renewal, Dispute
        'TrackingDate',
        'Status',       // Pending, Completed, Overdue, etc.
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function ip()
    {
        return $this->belongsTo(LegalIntellectualProperty::class, 'IPID');
    }
}
