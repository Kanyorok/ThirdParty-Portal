<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitPolicyNoticePeriod extends Model
{
    protected $table = 't_HRExitPolicyNoticePeriods';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PolicyID',
        'EmploymentType',
        'ContractType',
        'NoticeDays',
        'PayInLieuAllowed',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'PayInLieuAllowed' => 'boolean',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ExitPolicy::class, 'PolicyID');
    }
}
