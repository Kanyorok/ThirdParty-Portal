<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalObligationAssignment extends Model
{
    protected $table = 't_LegalObligationAssignments';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'ObligationID',
        'AssignedTo',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
        'IsActive',
    ];

    public function obligation(): BelongsTo
    {
        return $this->belongsTo(LegalObligation::class, 'ObligationID');
    }
}
