<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingLead extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $table = 't_MeetingLeads';

    protected $fillable = [
        'MeetingId', 'LeadId',
        'CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy'
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'LeadID', 'LeadId');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'MeetingId', 'MeetingID');
    }
}
