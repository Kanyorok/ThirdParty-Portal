<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingLead extends Model
{
    use UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    protected $table = 't_MeetingLeads';

    public static function getPrimaryKey(): string
    {
        return 'MeetingLeadsId';
    }

    protected $fillable = [
                           'MeetingId',
                           'LeadId',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                          ];

    protected $casts = [
                        'CreatedOn'  => 'datetime',
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
