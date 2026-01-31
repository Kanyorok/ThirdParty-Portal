<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleLead extends Model
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    protected $table = 't_ScheduleLeads';

    public static function getPrimaryKey(): string
    {
        return 'ScheduleLeadsId';
    }

    protected $fillable = [
                           'ScheduleId',
                           'LeadId',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                           'ReminderOn',
                          ];

    protected $casts = [
                        'CreatedOn' => 'datetime',
                        'ModifiedOn' => 'datetime',
                        'ReminderOn' => 'datetime',
                       ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'LeadId');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'ScheduleId', 'ScheduleID');
    }
}
